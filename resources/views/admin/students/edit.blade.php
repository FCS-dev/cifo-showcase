<x-layouts.admin :title="__('admin.students.edit') . ' · ' . $student->name">
    <x-admin.ui.breadcrumb :items="[
        ['label' => __('admin.nav.dashboard'), 'href' => route('dashboard')],
        ['label' => __('admin.students.title'), 'href' => route('students.index')],
        ['label' => $student->name],
        ['label' => __('admin.common.edit')],
    ]" />

    <div class="mb-6">
        <h1 class="text-2xl font-bold">{{ __('admin.students.edit') }}</h1>
        <p class="text-sm opacity-70">
            <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $student->name }}</code>
        </p>
    </div>

    <x-admin.ui.card class="max-w-lg">
        <form method="POST" action="{{ route('students.update', $student) }}" novalidate x-data="{
            form: $form('patch', '{{ route('students.update', $student) }}', {
                name: @js($student->name),
                email: @js($student->email)
            })
        }"
            @submit.prevent="form.submit({ onSuccess: () => window.location.href = '{{ route('students.index') }}' })">
            @csrf
            @method('PATCH')

            <div class="space-y-4">
                <x-admin.ui.input name="name" :label="__('admin.students.col_name')" icon="icofont-user" :placeholder="__('admin.students.name_placeholder')"
                    :required="true" x-model="form.name" @change="form.validate('name')" />

                <x-admin.ui.input name="email" :label="__('admin.students.col_email')" type="email" icon="icofont-email" :placeholder="__('admin.students.email_placeholder')"
                    :required="true" x-model="form.email" @change="form.validate('email')" />
            </div>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-1 text-xs opacity-50">
                <span><i class="icofont-calendar"></i> {{ __('admin.common.created_at') }}:
                    {{ $student->created_at->format('d/m/Y H:i') }}</span>
                <span><i class="icofont-clock-time"></i> {{ __('admin.common.updated_at') }}:
                    {{ $student->updated_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="flex justify-end gap-2 mt-8 pt-4 border-t border-base-300">
                <x-admin.ui.button variant="ghost" :href="route('students.index')">
                    {{ __('admin.common.cancel') }}
                </x-admin.ui.button>
                <x-admin.ui.button type="submit" icon="icofont-check-circled" x-bind:loading="form.processing">
                    {{ __('admin.common.save_changes') }}
                </x-admin.ui.button>
            </div>
        </form>
    </x-admin.ui.card>

    <x-admin.ui.card class="mt-6">
        <h2 class="text-lg font-semibold mb-1">{{ __('admin.students.projects_title') }}</h2>
        <p class="text-sm opacity-60 mb-4">{{ __('admin.students.projects_sub') }}</p>

        @if ($projects->isEmpty())
            <p class="text-sm opacity-50 flex items-center gap-2">
                <i class="icofont-info-circle"></i> {{ __('admin.students.projects_empty') }}
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('admin.students.col_project') }}</th>
                            <th>{{ __('admin.students.col_course') }}</th>
                            <th>{{ __('admin.students.col_status') }}</th>
                            <th>{{ __('admin.students.col_date') }}</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            @php
                                $canOpen = is_null($ownedCourseIds) || in_array($project->course_id, $ownedCourseIds, true);
                                $statusColor = match ($project->status) {
                                    'published' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'error',
                                    default => 'neutral',
                                };
                            @endphp
                            <tr class="hover">
                                <td class="font-medium text-sm">{{ $project->title }}</td>
                                <td class="text-sm">
                                    @if ($project->course)
                                        <code class="bg-base-200 px-1.5 py-0.5 rounded text-xs">{{ $project->course->course_code }}</code>
                                        <span class="ml-1">{{ $project->course->name }}</span>
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                                <td>
                                    <x-admin.ui.badge :color="$statusColor">
                                        {{ __('admin.projects.status_' . $project->status) }}
                                    </x-admin.ui.badge>
                                </td>
                                <td class="text-sm opacity-70">{{ $project->project_date->format('m/Y') }}</td>
                                <td class="text-right">
                                    @if ($canOpen)
                                        @can('projects.view')
                                            <a href="{{ route('admin.projects.show', $project) }}"
                                                class="btn btn-ghost btn-xs btn-square" title="{{ __('admin.common.view') }}">
                                                <i class="icofont-eye text-info text-base"></i>
                                            </a>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.ui.card>

</x-layouts.admin>
