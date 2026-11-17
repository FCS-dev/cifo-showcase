<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Student;
use App\Models\Tag;
use App\Models\User;
use App\Services\TeacherProjectScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(private TeacherProjectScope $teacherScope)
    {
    }

    public function index(Request $request): View
    {
        Gate::authorize('projects.view');

        $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:draft,pending,published,rejected'],
            'course' => ['nullable', 'integer'],
            'sort' => ['nullable', 'in:project_date,status,created_at'],
            'direction' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'in:5,10,25'],
        ]);

        $search = $request->query('search');
        $statusFilter = $request->query('status');
        $courseFilter = $request->query('course');
        $sort = $request->query('sort', 'project_date');
        $direction = $request->query('direction', 'desc');
        $perPage = (int) $request->query('per_page', 10);
        $locale = app()->getLocale();
        $user = $request->user();
        $isScoped = $this->teacherScope->appliesTo($user);
        $courseIds = $this->teacherScope->courseIds($user);

        if ($isScoped && $courseFilter && ! in_array((int) $courseFilter, $courseIds, true)) {
            abort(403);
        }

        $projects = Project::query()
            ->with(['course', 'tags'])
            ->when($isScoped, fn ($q) => $q->whereIn('course_id', $courseIds))
            ->when($search, fn ($q) => $q->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(`title`, ?))) LIKE ?',
                ['$.'.$locale, mb_strtolower("%{$search}%")]
            ))
            ->when($statusFilter, fn ($q) => $q->where('status', $statusFilter))
            ->when($courseFilter, fn ($q) => $q->where('course_id', $courseFilter))
            ->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $courses = $this->teacherScope->availableCourses($user, $locale);
        $canCreateProjects = ! $isScoped || $courseIds !== [];

        return view('admin.projects.index', compact('projects', 'courses', 'canCreateProjects'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        Gate::authorize('projects.create');

        $user = $request->user();
        $locale = app()->getLocale();

        if ($this->teacherScope->appliesTo($user) && $this->teacherScope->courseIds($user) === []) {
            return redirect()
                ->route('admin.projects.index')
                ->with('error', __('admin.projects.no_courses'));
        }

        $courseOptions = $this->courseOptions($user, $locale);
        $tags = Tag::orderBy("name->{$locale}")->get();
        $students = Student::orderBy('name')->get();

        return view('admin.projects.create', compact('courseOptions', 'tags', 'students'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Gate::authorize('projects.create');

        $validated = $request->validated();
        $this->ensureCourseAllowed($request->user(), (int) $validated['course_id']);
        $tags = $validated['tags'] ?? [];
        $students = $validated['students'] ?? [];
        $filesData = $validated['files'] ?? [];
        $featuredValue = $validated['featured_media'] ?? null;
        $validated['featured'] = $request->boolean('featured');
        $validated['project_date'] = $validated['project_date'].'-01';
        unset($validated['tags'], $validated['students'], $validated['images'], $validated['featured_media'], $validated['files']);

        $project = DB::transaction(function () use ($validated, $tags, $students, $filesData, $request, $featuredValue) {
            $project = Project::create($validated);
            $project->tags()->sync($tags);
            $project->students()->sync($students);
            $this->syncMedia($project, $request->file('images', []), $featuredValue);
            $this->syncFiles($project, $filesData);

            return $project;
        });

        return redirect()->route('admin.projects.index')->with('success', __('admin.projects.created'));
    }

    public function show(Request $request, Project $project): View
    {
        Gate::authorize('projects.view');
        $this->ensureProjectVisible($request->user(), $project);

        $project->load(['course', 'tags', 'students', 'files', 'media']);

        return view('admin.projects.show', compact('project'));
    }

    public function edit(Request $request, Project $project): View
    {
        Gate::authorize('projects.update');
        $this->ensureProjectVisible($request->user(), $project);

        $locale = app()->getLocale();
        $courseOptions = $this->courseOptions($request->user(), $locale);
        $tags = Tag::orderBy("name->{$locale}")->get();
        $selectedTags = $project->tags->pluck('id')->toArray();
        $students = Student::orderBy('name')->get();
        $selectedStudents = $project->students->pluck('id')->toArray();

        return view('admin.projects.edit', compact('project', 'courseOptions', 'tags', 'selectedTags', 'students', 'selectedStudents'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        Gate::authorize('projects.update');
        $this->ensureProjectVisible($request->user(), $project);

        $validated = $request->validated();
        $this->ensureCourseAllowed($request->user(), (int) $validated['course_id']);
        $tags = $validated['tags'] ?? [];
        $students = $validated['students'] ?? [];
        $filesData = $validated['files'] ?? [];
        $deleteIds = $validated['delete_media'] ?? [];
        $mediaOrder = $validated['media_order'] ?? [];
        $featuredValue = $validated['featured_media'] ?? null;
        $validated['featured'] = $request->boolean('featured');
        $validated['project_date'] = $validated['project_date'].'-01';
        unset($validated['tags'], $validated['students'], $validated['images'], $validated['delete_media'],
            $validated['media_order'], $validated['featured_media'], $validated['files']);

        DB::transaction(function () use ($project, $validated, $tags, $students, $filesData, $request, $deleteIds, $mediaOrder, $featuredValue) {
            $project->update($validated);
            $project->tags()->sync($tags);
            $project->students()->sync($students);

            if ($deleteIds) {
                $project->media()
                    ->whereIn('id', $deleteIds)
                    ->get()
                    ->each(fn ($m) => $m->delete());
            }

            foreach ($mediaOrder as $position => $mediaId) {
                $project->media()->where('id', $mediaId)->update(['order_column' => $position + 1]);
            }

            if ($featuredValue && is_numeric($featuredValue)) {
                $project->getMedia('images')->each(function ($m) use ($featuredValue) {
                    $m->setCustomProperty('is_featured', $m->id === (int) $featuredValue)->save();
                });
            }

            $this->syncMedia($project, $request->file('images', []), $featuredValue);
            $this->syncFiles($project, $filesData, existing: true);
        });

        return redirect()->route('admin.projects.index')->with('success', __('admin.projects.updated'));
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        Gate::authorize('projects.delete');
        $this->ensureProjectVisible($request->user(), $project);

        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', __('admin.projects.deleted'));
    }

    private function ensureCourseAllowed(User $user, int $courseId): void
    {
        if (! $this->teacherScope->appliesTo($user)) {
            return;
        }

        if (! in_array($courseId, $this->teacherScope->courseIds($user), true)) {
            throw ValidationException::withMessages([
                'course_id' => __('admin.projects.course_not_allowed'),
            ]);
        }
    }

    private function ensureProjectVisible(User $user, Project $project): void
    {
        abort_unless($this->teacherScope->allowsProject($user, $project), 404);
    }

    /**
     * @return array<int,string>
     */
    private function courseOptions(User $user, string $locale): array
    {
        return $this->teacherScope->availableCourses($user, $locale)
            ->mapWithKeys(fn ($course) => [$course->id => "[{$course->course_code}] {$course->name}"])
            ->toArray();
    }

    /**
     * Add newly uploaded image files to a project's media collection.
     *
     * @param  array<int,UploadedFile>  $files
     * @param  string|null  $featuredValue  e.g. "new:0" or numeric media id
     */
    private function syncMedia(Project $project, array $files, ?string $featuredValue): void
    {
        foreach ($files as $index => $file) {
            $isFeatured = $featuredValue === "new:{$index}";
            $project->addMedia($file)
                ->withCustomProperties(['is_featured' => $isFeatured])
                ->toMediaCollection('images');
        }

        $hasAnyFeatured = $project->getMedia('images')
            ->contains(fn ($m) => (bool) $m->getCustomProperty('is_featured'));

        if (! $hasAnyFeatured && $project->getMedia('images')->isNotEmpty()) {
            $project->getFirstMedia('images')->setCustomProperty('is_featured', true)->save();
        }
    }

    /**
     * Sync the project_files rows from submitted form data.
     *
     * @param  array<int,array<string,mixed>>  $filesData
     * @param  bool  $existing  When true, delete rows not present in submission.
     */
    private function syncFiles(Project $project, array $filesData, bool $existing = false): void
    {
        if ($existing) {
            $submittedIds = collect($filesData)
                ->pluck('id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->toArray();

            $project->files()->whereNotIn('id', $submittedIds)->delete();
        }

        foreach ($filesData as $index => $fileData) {
            $id = isset($fileData['id']) ? (int) $fileData['id'] : null;
            $attrs = [
                'type' => $fileData['type'],
                'url' => $fileData['url'],
                'label' => $fileData['label'] ?? null,
                'sort_order' => $index,
            ];

            if ($id) {
                $project->files()->where('id', $id)->update($attrs);
            } else {
                $project->files()->create($attrs);
            }
        }
    }
}
