@extends('layouts.app-master')

@section('title', 'Create Template')
@section('page-icon', '✨')
@section('page-title', 'Create Template')

@section('content')
<div x-data="templateBuilder()" class="animate-fade-in max-w-4xl mx-auto">
    <div class="glass-card rounded-xl p-8">
        <form action="{{ route('templates.store') }}" method="POST" @submit="prepareSubmit">
            @csrf

            <!-- Template Info -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-white mb-6">Template Information</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Template Name -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Template Name *</label>
                        <input type="text" 
                               name="name" 
                               x-model="template.name"
                               required
                               placeholder="e.g., Morning Routine"
                               class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        @error('name')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
                        <select name="category" 
                                x-model="template.category"
                                required
                                class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Icon (Emoji) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Icon (Emoji)</label>
                        <input type="text" 
                               name="icon" 
                               x-model="template.icon"
                               maxlength="10"
                               placeholder="e.g., 🌅"
                               class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                        <textarea name="description" 
                                  x-model="template.description"
                                  rows="3"
                                  placeholder="Describe what this template is for..."
                                  class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>

                    <!-- Make Public -->
                    <div class="md:col-span-2">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" 
                                   name="is_public" 
                                   x-model="template.is_public"
                                   class="w-5 h-5 rounded bg-white/10 border-white/20 text-purple-600 focus:ring-purple-500">
                            <span class="text-gray-300">Make this template public (other users can use it)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tasks Builder -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-white">Template Tasks</h2>
                    <button type="button" 
                            @click="addTask"
                            class="glass-button text-white px-4 py-2 rounded-lg font-medium flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Add Task</span>
                    </button>
                </div>

                <!-- Task List -->
                <div class="space-y-4">
                    <template x-for="(task, index) in template.tasks" :key="index">
                        <div class="p-6 bg-white/5 rounded-lg border border-white/10">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-white font-medium">Task <span x-text="index + 1"></span></h3>
                                <button type="button" 
                                        @click="removeTask(index)"
                                        class="text-red-400 hover:text-red-300 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                <!-- Task Title -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                                    <input type="text" 
                                           :name="`tasks[${index}][title]`"
                                           x-model="task.title"
                                           required
                                           placeholder="e.g., Review daily goals"
                                           class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                </div>

                                <!-- Task Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                                    <textarea :name="`tasks[${index}][description]`"
                                              x-model="task.description"
                                              rows="2"
                                              placeholder="Optional task details..."
                                              class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Priority -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Priority *</label>
                                        <select :name="`tasks[${index}][priority]`"
                                                x-model="task.priority"
                                                required
                                                class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="low">Low</option>
                                            <option value="medium" selected>Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>

                                    <!-- Due Date Offset -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-2">Due in (days)</label>
                                        <input type="number" 
                                               :name="`tasks[${index}][due_offset]`"
                                               x-model="task.due_offset"
                                               placeholder="e.g., 0 (today)"
                                               min="0"
                                               class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg text-white placeholder-gray-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div x-show="template.tasks.length === 0" class="text-center py-12 bg-white/5 rounded-lg border border-white/10">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-400">No tasks yet. Click "Add Task" to get started.</p>
                </div>
            </div>

            <!-- Variable Helper -->
            <div class="mb-8 p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg">
                <h3 class="text-sm font-medium text-blue-400 mb-2">💡 Available Variables</h3>
                <p class="text-xs text-gray-300 mb-2">Use these variables in task titles and descriptions:</p>
                <div class="flex flex-wrap gap-2 text-xs">
                    <code class="px-2 py-1 bg-white/10 rounded text-purple-400">{date}</code>
                    <code class="px-2 py-1 bg-white/10 rounded text-purple-400">{time}</code>
                    <code class="px-2 py-1 bg-white/10 rounded text-purple-400">{week}</code>
                    <code class="px-2 py-1 bg-white/10 rounded text-purple-400">{month}</code>
                    <code class="px-2 py-1 bg-white/10 rounded text-purple-400">{day}</code>
                    <code class="px-2 py-1 bg-white/10 rounded text-purple-400">{year}</code>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-4">
                <button type="submit" 
                        class="flex-1 glass-button text-white px-6 py-3 rounded-xl font-bold">
                    Create Template
                </button>
                <a href="{{ route('templates.index') }}" 
                   class="px-6 py-3 rounded-xl font-medium bg-white/5 hover:bg-white/10 text-white transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function templateBuilder() {
    return {
        template: {
            name: '',
            description: '',
            category: 'other',
            icon: '',
            is_public: false,
            tasks: []
        },

        addTask() {
            this.template.tasks.push({
                title: '',
                description: '',
                priority: 'medium',
                due_offset: 0
            });
        },

        removeTask(index) {
            this.template.tasks.splice(index, 1);
        },

        prepareSubmit(e) {
            if (this.template.tasks.length === 0) {
                e.preventDefault();
                alert('Please add at least one task to the template.');
                return false;
            }
        }
    }
}
</script>
@endsection
