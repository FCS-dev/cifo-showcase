<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TeacherProjectScope
{
    public function appliesTo(User $user): bool
    {
        return ! $user->isAdmin() && $user->hasRole('Teacher');
    }

    /**
     * @return array<int,int>
     */
    public function courseIds(User $user): array
    {
        if (! $this->appliesTo($user)) {
            return [];
        }

        return $user->teacher?->courses()->pluck('courses.id')->all() ?? [];
    }

    public function allowsProject(User $user, Project $project): bool
    {
        return ! $this->appliesTo($user)
            || in_array($project->course_id, $this->courseIds($user), true);
    }

    /**
     * @return Collection<int,Course>
     */
    public function availableCourses(User $user, string $locale): Collection
    {
        return Course::query()
            ->when(
                $this->appliesTo($user),
                fn ($query) => $query->whereIn('id', $this->courseIds($user))
            )
            ->orderBy("name->{$locale}")
            ->get();
    }
}
