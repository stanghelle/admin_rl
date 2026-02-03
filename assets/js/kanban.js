/**
 * Kanban Board JavaScript
 * Handles drag & drop, CRUD operations, and UI interactions
 */

// API Base URL - Change this to match your server
const API_BASE = 'api';

// State
let columns = [];
let draggedTask = null;
let draggedElement = null;

// DOM Elements
const kanbanContainer = document.getElementById('kanban-container');
const taskModal = document.getElementById('task-modal');
const columnModal = document.getElementById('column-modal');
const deleteModal = document.getElementById('delete-modal');
const toastContainer = document.getElementById('toast-container');

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    loadBoard();
    setupEventListeners();
    setupThemeToggle();
    setupMobileMenu();
});

// ============================================
// API Functions
// ============================================

async function fetchAPI(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE}/${endpoint}`, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showToast('An error occurred. Please try again.', 'error');
        throw error;
    }
}

async function loadBoard() {
    showLoading();
    try {
        columns = await fetchAPI('columns.php?board_id=1');
        renderBoard();
    } catch (error) {
        // For demo purposes, load sample data if API fails
        columns = getSampleData();
        renderBoard();
        showToast('Using demo data (API not connected)', 'warning');
    }
}

async function createTask(taskData) {
    try {
        const newTask = await fetchAPI('tasks.php', {
            method: 'POST',
            body: JSON.stringify(taskData)
        });

        const column = columns.find(c => c.id == taskData.column_id);
        if (column) {
            column.tasks.push(newTask);
            renderBoard();
        }

        showToast('Task created successfully!', 'success');
        return newTask;
    } catch (error) {
        // Demo mode
        const newTask = {
            id: Date.now(),
            ...taskData,
            position: 0
        };

        const column = columns.find(c => c.id == taskData.column_id);
        if (column) {
            column.tasks.push(newTask);
            renderBoard();
        }

        showToast('Task created (demo mode)', 'success');
        return newTask;
    }
}

async function updateTask(taskData) {
    try {
        await fetchAPI('tasks.php', {
            method: 'PUT',
            body: JSON.stringify(taskData)
        });

        // Update local state
        for (const column of columns) {
            const taskIndex = column.tasks.findIndex(t => t.id == taskData.id);
            if (taskIndex > -1) {
                column.tasks[taskIndex] = { ...column.tasks[taskIndex], ...taskData };
                break;
            }
        }

        renderBoard();
        showToast('Task updated successfully!', 'success');
    } catch (error) {
        // Demo mode
        for (const column of columns) {
            const taskIndex = column.tasks.findIndex(t => t.id == taskData.id);
            if (taskIndex > -1) {
                column.tasks[taskIndex] = { ...column.tasks[taskIndex], ...taskData };
                break;
            }
        }
        renderBoard();
        showToast('Task updated (demo mode)', 'success');
    }
}

async function deleteTask(taskId) {
    try {
        await fetchAPI(`tasks.php?id=${taskId}`, {
            method: 'DELETE'
        });

        // Remove from local state
        for (const column of columns) {
            const taskIndex = column.tasks.findIndex(t => t.id == taskId);
            if (taskIndex > -1) {
                column.tasks.splice(taskIndex, 1);
                break;
            }
        }

        renderBoard();
        showToast('Task deleted successfully!', 'success');
    } catch (error) {
        // Demo mode
        for (const column of columns) {
            const taskIndex = column.tasks.findIndex(t => t.id == taskId);
            if (taskIndex > -1) {
                column.tasks.splice(taskIndex, 1);
                break;
            }
        }
        renderBoard();
        showToast('Task deleted (demo mode)', 'success');
    }
}

async function moveTask(taskId, newColumnId, newPosition) {
    try {
        await fetchAPI('reorder.php', {
            method: 'POST',
            body: JSON.stringify({
                task_id: taskId,
                new_column_id: newColumnId,
                new_position: newPosition
            })
        });
    } catch (error) {
        console.log('Reorder API not available, using local state');
    }
}

async function createColumn(columnData) {
    try {
        const newColumn = await fetchAPI('columns.php', {
            method: 'POST',
            body: JSON.stringify(columnData)
        });

        columns.push({ ...newColumn, tasks: [] });
        renderBoard();
        showToast('Column created successfully!', 'success');
        return newColumn;
    } catch (error) {
        // Demo mode
        const newColumn = {
            id: Date.now(),
            ...columnData,
            position: columns.length,
            tasks: []
        };

        columns.push(newColumn);
        renderBoard();
        showToast('Column created (demo mode)', 'success');
        return newColumn;
    }
}

async function deleteColumn(columnId) {
    try {
        await fetchAPI(`columns.php?id=${columnId}`, {
            method: 'DELETE'
        });

        columns = columns.filter(c => c.id != columnId);
        renderBoard();
        showToast('Column deleted successfully!', 'success');
    } catch (error) {
        // Demo mode
        columns = columns.filter(c => c.id != columnId);
        renderBoard();
        showToast('Column deleted (demo mode)', 'success');
    }
}

// ============================================
// Render Functions
// ============================================

function renderBoard() {
    kanbanContainer.innerHTML = '';

    columns.forEach(column => {
        const columnEl = createColumnElement(column);
        kanbanContainer.appendChild(columnEl);
    });

    // Add "Add Column" button
    const addColumnBtn = document.createElement('button');
    addColumnBtn.className = 'add-column-btn';
    addColumnBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Add Column';
    addColumnBtn.onclick = () => openColumnModal();
    kanbanContainer.appendChild(addColumnBtn);

    setupDragAndDrop();
}

function createColumnElement(column) {
    const columnEl = document.createElement('div');
    columnEl.className = 'kanban-column';
    columnEl.dataset.columnId = column.id;

    const tasksHtml = column.tasks.map(task => createTaskHtml(task)).join('');

    columnEl.innerHTML = `
        <div class="column-header">
            <div class="column-title">
                <span style="width: 8px; height: 8px; border-radius: 50%; background-color: ${column.color || '#6c757d'};"></span>
                ${escapeHtml(column.name)}
                <span class="column-badge">${column.tasks.length}</span>
            </div>
            <div class="column-actions">
                <button class="column-action-btn" onclick="openTaskModal(${column.id})" title="Add Task">
                    <i class="bi bi-plus-lg"></i>
                </button>
                <button class="column-action-btn" onclick="confirmDeleteColumn(${column.id})" title="Delete Column">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
        <div class="column-tasks" data-column-id="${column.id}">
            ${tasksHtml}
        </div>
        <button class="add-task-btn" onclick="openTaskModal(${column.id})">
            <i class="bi bi-plus"></i> Add Task
        </button>
    `;

    return columnEl;
}

function createTaskHtml(task) {
    const labelsHtml = (task.labels || []).map(label =>
        `<span class="task-label label-${label}">${escapeHtml(label)}</span>`
    ).join('');

    const priorityClass = `priority-${task.priority || 'medium'}`;
    const priorityText = (task.priority || 'medium').charAt(0).toUpperCase() + (task.priority || 'medium').slice(1);

    let dueDateHtml = '';
    if (task.due_date) {
        const dueDate = new Date(task.due_date);
        dueDateHtml = `
            <span class="task-due-date">
                <i class="bi bi-calendar3"></i>
                ${dueDate.toLocaleDateString()}
            </span>
        `;
    }

    return `
        <div class="task-card" data-task-id="${task.id}" draggable="true">
            ${labelsHtml ? `<div class="task-labels">${labelsHtml}</div>` : ''}
            <div class="task-title">${escapeHtml(task.title)}</div>
            ${task.description ? `<div class="task-description">${escapeHtml(task.description)}</div>` : ''}
            <div class="task-meta">
                <div>
                    <span class="task-priority ${priorityClass}">
                        <i class="bi bi-flag-fill"></i>
                        ${priorityText}
                    </span>
                    ${dueDateHtml}
                </div>
                <div class="task-actions">
                    <button class="task-action-btn" onclick="editTask(${task.id})" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="task-action-btn delete" onclick="confirmDeleteTask(${task.id})" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}

function showLoading() {
    kanbanContainer.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
        </div>
    `;
}

// ============================================
// Drag and Drop
// ============================================

function setupDragAndDrop() {
    const taskCards = document.querySelectorAll('.task-card');
    const columnTasks = document.querySelectorAll('.column-tasks');

    taskCards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
    });

    columnTasks.forEach(column => {
        column.addEventListener('dragover', handleDragOver);
        column.addEventListener('dragenter', handleDragEnter);
        column.addEventListener('dragleave', handleDragLeave);
        column.addEventListener('drop', handleDrop);
    });
}

function handleDragStart(e) {
    draggedElement = e.target;
    draggedTask = {
        id: parseInt(e.target.dataset.taskId),
        columnId: parseInt(e.target.closest('.column-tasks').dataset.columnId)
    };

    e.target.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', e.target.dataset.taskId);

    // Create visual feedback
    setTimeout(() => {
        e.target.style.opacity = '0.5';
    }, 0);
}

function handleDragEnd(e) {
    e.target.classList.remove('dragging');
    e.target.style.opacity = '1';

    document.querySelectorAll('.column-tasks').forEach(col => {
        col.classList.remove('drag-over');
    });

    draggedElement = null;
    draggedTask = null;
}

function handleDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';

    const columnTasks = e.currentTarget;
    const afterElement = getDragAfterElement(columnTasks, e.clientY);
    const draggable = document.querySelector('.dragging');

    if (draggable) {
        if (afterElement == null) {
            columnTasks.appendChild(draggable);
        } else {
            columnTasks.insertBefore(draggable, afterElement);
        }
    }
}

function handleDragEnter(e) {
    e.preventDefault();
    e.currentTarget.classList.add('drag-over');
}

function handleDragLeave(e) {
    // Only remove class if we're actually leaving the column
    if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.classList.remove('drag-over');
    }
}

function handleDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');

    const newColumnId = parseInt(e.currentTarget.dataset.columnId);
    const taskId = parseInt(e.dataTransfer.getData('text/plain'));

    // Calculate new position
    const taskCards = Array.from(e.currentTarget.querySelectorAll('.task-card'));
    const newPosition = taskCards.findIndex(card => parseInt(card.dataset.taskId) === taskId);

    // Update local state
    let movedTask = null;

    // Remove from old column
    for (const column of columns) {
        const taskIndex = column.tasks.findIndex(t => t.id == taskId);
        if (taskIndex > -1) {
            movedTask = column.tasks.splice(taskIndex, 1)[0];
            break;
        }
    }

    // Add to new column
    if (movedTask) {
        const newColumn = columns.find(c => c.id == newColumnId);
        if (newColumn) {
            movedTask.column_id = newColumnId;
            movedTask.position = newPosition >= 0 ? newPosition : newColumn.tasks.length;
            newColumn.tasks.splice(movedTask.position, 0, movedTask);

            // Update positions
            newColumn.tasks.forEach((task, idx) => {
                task.position = idx;
            });
        }
    }

    // Call API to persist
    moveTask(taskId, newColumnId, newPosition >= 0 ? newPosition : 0);

    // Re-render to update badges
    renderBoard();
}

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll('.task-card:not(.dragging)')];

    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

// ============================================
// Modal Functions
// ============================================

let currentEditingTaskId = null;
let currentColumnId = null;
let deleteTarget = null;

function openTaskModal(columnId, taskId = null) {
    currentColumnId = columnId;
    currentEditingTaskId = taskId;

    const form = document.getElementById('task-form');
    const modalTitle = document.getElementById('task-modal-title');

    if (taskId) {
        modalTitle.textContent = 'Edit Task';
        // Find task and populate form
        for (const column of columns) {
            const task = column.tasks.find(t => t.id == taskId);
            if (task) {
                form.title.value = task.title || '';
                form.description.value = task.description || '';
                form.priority.value = task.priority || 'medium';
                form.due_date.value = task.due_date || '';
                form.assigned_to.value = task.assigned_to || '';
                form.labels.value = (task.labels || []).join(', ');
                break;
            }
        }
    } else {
        modalTitle.textContent = 'Add New Task';
        form.reset();
    }

    taskModal.classList.add('active');
}

function closeTaskModal() {
    taskModal.classList.remove('active');
    currentEditingTaskId = null;
    currentColumnId = null;
}

function openColumnModal() {
    document.getElementById('column-form').reset();
    columnModal.classList.add('active');
}

function closeColumnModal() {
    columnModal.classList.remove('active');
}

function confirmDeleteTask(taskId) {
    deleteTarget = { type: 'task', id: taskId };
    document.getElementById('delete-message').textContent = 'Are you sure you want to delete this task? This action cannot be undone.';
    deleteModal.classList.add('active');
}

function confirmDeleteColumn(columnId) {
    const column = columns.find(c => c.id == columnId);
    deleteTarget = { type: 'column', id: columnId };
    document.getElementById('delete-message').textContent = `Are you sure you want to delete the "${column.name}" column and all its tasks? This action cannot be undone.`;
    deleteModal.classList.add('active');
}

function closeDeleteModal() {
    deleteModal.classList.remove('active');
    deleteTarget = null;
}

function confirmDelete() {
    if (deleteTarget) {
        if (deleteTarget.type === 'task') {
            deleteTask(deleteTarget.id);
        } else if (deleteTarget.type === 'column') {
            deleteColumn(deleteTarget.id);
        }
    }
    closeDeleteModal();
}

function editTask(taskId) {
    for (const column of columns) {
        const task = column.tasks.find(t => t.id == taskId);
        if (task) {
            openTaskModal(column.id, taskId);
            break;
        }
    }
}

// ============================================
// Form Handlers
// ============================================

function setupEventListeners() {
    // Task form submit
    document.getElementById('task-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const labelsString = formData.get('labels') || '';
        const labels = labelsString.split(',').map(l => l.trim().toLowerCase()).filter(l => l);

        const taskData = {
            title: formData.get('title'),
            description: formData.get('description'),
            priority: formData.get('priority'),
            due_date: formData.get('due_date') || null,
            assigned_to: formData.get('assigned_to') || null,
            labels: labels
        };

        if (currentEditingTaskId) {
            taskData.id = currentEditingTaskId;
            await updateTask(taskData);
        } else {
            taskData.column_id = currentColumnId;
            await createTask(taskData);
        }

        closeTaskModal();
    });

    // Column form submit
    document.getElementById('column-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        await createColumn({
            name: formData.get('name'),
            color: formData.get('color'),
            board_id: 1
        });

        closeColumnModal();
    });

    // Close modals on overlay click
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // Close modals on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                modal.classList.remove('active');
            });
        }
    });
}

// ============================================
// Theme Toggle
// ============================================

function setupThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'light';

    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);

    themeToggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const newTheme = current === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('#theme-toggle i');
    icon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
}

// ============================================
// Mobile Menu
// ============================================

function setupMobileMenu() {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.querySelector('.sidebar-wrapper');
    const overlay = document.querySelector('.sidebar-overlay');

    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
        });
    }
}

// ============================================
// Toast Notifications
// ============================================

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'}"></i>
        <span>${escapeHtml(message)}</span>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ============================================
// Utility Functions
// ============================================

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Sample data for demo mode (when API is not available)
function getSampleData() {
    return [
        {
            id: 1,
            name: 'To Do',
            position: 0,
            color: '#6c757d',
            tasks: [
                {
                    id: 1,
                    title: 'Design Homepage',
                    description: 'Create wireframes and mockups for the new homepage',
                    priority: 'high',
                    position: 0,
                    labels: ['design', 'ui']
                },
                {
                    id: 2,
                    title: 'Setup Database',
                    description: 'Configure MySQL and create tables',
                    priority: 'medium',
                    position: 1,
                    labels: ['backend']
                }
            ]
        },
        {
            id: 2,
            name: 'In Progress',
            position: 1,
            color: '#0d6efd',
            tasks: [
                {
                    id: 3,
                    title: 'Implement Authentication',
                    description: 'Add login and registration functionality',
                    priority: 'high',
                    position: 0,
                    labels: ['backend', 'security']
                },
                {
                    id: 4,
                    title: 'Create API Endpoints',
                    description: 'Build RESTful API for task management',
                    priority: 'medium',
                    position: 1,
                    labels: ['backend', 'api']
                }
            ]
        },
        {
            id: 3,
            name: 'Review',
            position: 2,
            color: '#ffc107',
            tasks: [
                {
                    id: 5,
                    title: 'Code Review',
                    description: 'Review pull requests from team members',
                    priority: 'low',
                    position: 0,
                    labels: ['review']
                }
            ]
        },
        {
            id: 4,
            name: 'Done',
            position: 3,
            color: '#198754',
            tasks: [
                {
                    id: 6,
                    title: 'Project Setup',
                    description: 'Initialize project structure and dependencies',
                    priority: 'low',
                    position: 0,
                    labels: ['setup']
                }
            ]
        }
    ];
}
