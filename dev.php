<?php
require_once 'core/init.php';
include 'core/db_con.php';

// Require authentication
Auth::requireLogin();

$db = DB::getInstance();
?>
<!doctype html>
<html lang="en" data-bs-theme="blue-theme">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kanban Board | Project Management</title>
  <!--favicon-->
	<link rel="icon" href="assets/images/favicon-32x32.png" type="image/png">
  <!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet">
	<script src="assets/js/pace.min.js"></script>

  <!--plugins-->
  <link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/metisMenu.min.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/metismenu/mm-vertical.css">
  <link rel="stylesheet" type="text/css" href="assets/plugins/simplebar/css/simplebar.css">
  <!--bootstrap css-->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Material+Icons+Outlined" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
  <!--main css-->
  <link href="assets/css/bootstrap-extended.css" rel="stylesheet">
  <link href="sass/main.css" rel="stylesheet">
  <link href="sass/dark-theme.css" rel="stylesheet">
  <link href="sass/blue-theme.css" rel="stylesheet">
  <link href="sass/semi-dark.css" rel="stylesheet">
  <link href="sass/bordered-theme.css" rel="stylesheet">
  <link href="sass/responsive.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.min.css">
  <link rel='stylesheet' href='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.css'>

  <!-- Kanban Board Styles -->
  <style>
    /* Kanban Board Styles */
    .kanban-container {
      display: flex;
      gap: 1rem;
      overflow-x: auto;
      padding-bottom: 1rem;
      min-height: 65vh;
    }

    .kanban-column {
      min-width: 300px;
      max-width: 300px;
      background-color: var(--bs-body-bg);
      border-radius: 0.5rem;
      display: flex;
      flex-direction: column;
      border: 1px solid var(--bs-border-color);
    }

    .column-header {
      padding: 1rem;
      border-bottom: 1px solid var(--bs-border-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
      background-color: var(--bs-tertiary-bg);
      border-radius: 0.5rem 0.5rem 0 0;
    }

    .column-title {
      font-size: 0.95rem;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      color: var(--bs-heading-color);
    }

    .column-badge {
      background: linear-gradient(310deg, #7928ca, #ff0080);
      color: white;
      font-size: 0.7rem;
      padding: 0.15rem 0.5rem;
      border-radius: 1rem;
      font-weight: 500;
    }

    .column-actions {
      display: flex;
      gap: 0.25rem;
    }

    .column-action-btn {
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      background: transparent;
      border-radius: 50%;
      cursor: pointer;
      color: var(--bs-body-color);
      transition: all 0.2s;
    }

    .column-action-btn:hover {
      background-color: var(--bs-transparent-bg);
      color: var(--bs-heading-color);
    }

    .column-tasks {
      flex: 1;
      padding: 0.75rem;
      overflow-y: auto;
      min-height: 150px;
      max-height: 55vh;
    }

    .column-tasks.drag-over {
      background-color: rgba(121, 40, 202, 0.1);
    }

    /* Task Card Styles */
    .task-card {
      background-image: linear-gradient(127.09deg, rgba(6, 11, 40, 0.94) 19.41%, rgba(10, 14, 35, 0.49) 76.65%);
      border-radius: 0.5rem;
      padding: 1rem;
      margin-bottom: 0.75rem;
      cursor: grab;
      transition: all 0.2s;
      border: 1px solid var(--bs-border-color);
    }

    .task-card:hover {
      box-shadow: 0 4px 15px rgba(121, 40, 202, 0.3);
      border-color: #7928ca;
      transform: translateY(-2px);
    }

    .task-card.dragging {
      opacity: 0.5;
      transform: rotate(3deg);
      cursor: grabbing;
    }

    .task-labels {
      display: flex;
      gap: 0.25rem;
      flex-wrap: wrap;
      margin-bottom: 0.5rem;
    }

    .task-label {
      font-size: 0.65rem;
      padding: 0.15rem 0.5rem;
      border-radius: 0.25rem;
      font-weight: 500;
      text-transform: uppercase;
    }

    .label-design { background: linear-gradient(310deg, #7928ca, #ff0080); color: #fff; }
    .label-ui { background: linear-gradient(310deg, #17ad37, #98ec2d); color: #fff; }
    .label-backend { background: linear-gradient(310deg, #2152ff, #21d4fd); color: #fff; }
    .label-security { background: linear-gradient(310deg, #ea0606, #ff667c); color: #fff; }
    .label-api { background: linear-gradient(310deg, #f5365c, #f56036); color: #fff; }
    .label-review { background: linear-gradient(310deg, #627594, #a8b8d8); color: #fff; }
    .label-setup { background: linear-gradient(310deg, #141727, #3a416f); color: #fff; }
    .label-default { background-color: var(--bs-transparent-bg); color: var(--bs-body-color); }

    .task-title {
      font-size: 0.9rem;
      font-weight: 500;
      margin-bottom: 0.5rem;
      color: var(--bs-heading-color);
    }

    .task-description {
      font-size: 0.8rem;
      color: var(--bs-body-color);
      margin-bottom: 0.75rem;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      opacity: 0.8;
    }

    .task-meta {
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 0.75rem;
      color: var(--bs-body-color);
    }

    .task-priority {
      display: inline-flex;
      align-items: center;
      gap: 0.25rem;
      padding: 0.2rem 0.5rem;
      border-radius: 0.25rem;
      font-size: 0.7rem;
      font-weight: 500;
    }

    .priority-high { background: linear-gradient(310deg, #ea0606, #ff667c); color: #fff; }
    .priority-medium { background: linear-gradient(310deg, #f5365c, #f56036); color: #fff; }
    .priority-low { background: linear-gradient(310deg, #17ad37, #98ec2d); color: #fff; }

    .task-due-date {
      display: flex;
      align-items: center;
      gap: 0.25rem;
      opacity: 0.8;
    }

    .task-actions {
      display: flex;
      gap: 0.25rem;
      opacity: 0;
      transition: opacity 0.2s;
    }

    .task-card:hover .task-actions {
      opacity: 1;
    }

    .task-action-btn {
      width: 26px;
      height: 26px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      background: var(--bs-transparent-bg);
      border-radius: 0.25rem;
      cursor: pointer;
      color: var(--bs-body-color);
      transition: all 0.2s;
    }

    .task-action-btn:hover {
      background: linear-gradient(310deg, #7928ca, #ff0080);
      color: white;
    }

    .task-action-btn.delete:hover {
      background: linear-gradient(310deg, #ea0606, #ff667c);
    }

    /* Add Task Button */
    .add-task-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
      padding: 0.75rem;
      margin: 0.75rem;
      margin-top: 0;
      width: calc(100% - 1.5rem);
      border: 2px dashed var(--bs-border-color);
      background: transparent;
      border-radius: 0.5rem;
      color: var(--bs-body-color);
      font-size: 0.85rem;
      cursor: pointer;
      transition: all 0.2s;
    }

    .add-task-btn:hover {
      border-color: #7928ca;
      color: #ff0080;
      background-color: rgba(121, 40, 202, 0.1);
    }

    /* Add Column Button */
    .add-column-btn {
      min-width: 300px;
      max-width: 300px;
      height: fit-content;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 2rem;
      border: 2px dashed var(--bs-border-color);
      background: var(--bs-transparent-bg);
      border-radius: 0.5rem;
      color: var(--bs-body-color);
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.2s;
    }

    .add-column-btn:hover {
      border-color: #7928ca;
      color: #ff0080;
      background-color: rgba(121, 40, 202, 0.1);
    }

    /* Modal Styles */
    .kanban-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1050;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s;
    }

    .kanban-modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .kanban-modal-content {
      background-image: linear-gradient(127.09deg, rgba(6, 11, 40, 0.94) 19.41%, rgba(10, 14, 35, 0.69) 76.65%);
      border: 1px solid var(--bs-border-color);
      border-radius: 0.75rem;
      width: 100%;
      max-width: 500px;
      max-height: 90vh;
      overflow-y: auto;
      transform: translateY(-20px);
      transition: transform 0.3s;
    }

    .kanban-modal-overlay.active .kanban-modal-content {
      transform: translateY(0);
    }

    .kanban-modal-header {
      padding: 1rem 1.5rem;
      border-bottom: 1px solid var(--bs-border-color);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .kanban-modal-title {
      font-size: 1.1rem;
      font-weight: 600;
      margin: 0;
      color: var(--bs-heading-color);
    }

    .kanban-modal-close {
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      background: transparent;
      border-radius: 50%;
      cursor: pointer;
      color: var(--bs-body-color);
      font-size: 1.25rem;
      transition: all 0.2s;
    }

    .kanban-modal-close:hover {
      background-color: var(--bs-transparent-bg);
      color: #ff0080;
    }

    .kanban-modal-body {
      padding: 1.5rem;
    }

    .kanban-modal-footer {
      padding: 1rem 1.5rem;
      border-top: 1px solid var(--bs-border-color);
      display: flex;
      gap: 0.75rem;
      justify-content: flex-end;
    }

    /* Form Styles */
    .kanban-form-group {
      margin-bottom: 1rem;
    }

    .kanban-form-label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
      color: var(--bs-heading-color);
      font-size: 0.9rem;
    }

    .kanban-form-control {
      width: 100%;
      padding: 0.625rem 0.875rem;
      font-size: 0.9rem;
      border: 1px solid var(--bs-border-color);
      border-radius: 0.5rem;
      background-color: var(--bs-body-bg);
      color: var(--bs-body-color);
      transition: border-color 0.2s, box-shadow 0.2s;
      font-family: inherit;
    }

    .kanban-form-control:focus {
      outline: none;
      border-color: #7928ca;
      box-shadow: 0 0 0 3px rgba(121, 40, 202, 0.2);
    }

    .kanban-form-control::placeholder {
      color: var(--bs-body-color);
      opacity: 0.5;
    }

    textarea.kanban-form-control {
      min-height: 100px;
      resize: vertical;
    }

    /* Loading Spinner */
    .kanban-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem;
      width: 100%;
    }

    .kanban-spinner {
      width: 40px;
      height: 40px;
      border: 3px solid var(--bs-border-color);
      border-top: 3px solid #7928ca;
      border-radius: 50%;
      animation: kanban-spin 1s linear infinite;
    }

    @keyframes kanban-spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Toast Styles */
    .kanban-toast-container {
      position: fixed;
      bottom: 1.5rem;
      right: 1.5rem;
      z-index: 1100;
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
    }

    .kanban-toast {
      padding: 1rem 1.5rem;
      background: linear-gradient(310deg, #141727, #3a416f);
      color: white;
      border-radius: 0.5rem;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
      display: flex;
      align-items: center;
      gap: 0.75rem;
      animation: kanban-slideIn 0.3s ease-out;
    }

    .kanban-toast.success { background: linear-gradient(310deg, #17ad37, #98ec2d); }
    .kanban-toast.error { background: linear-gradient(310deg, #ea0606, #ff667c); }
    .kanban-toast.warning { background: linear-gradient(310deg, #f5365c, #f56036); }

    @keyframes kanban-slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    /* Color Indicator */
    .color-indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* Scrollbar Styling */
    .column-tasks::-webkit-scrollbar {
      width: 6px;
    }

    .column-tasks::-webkit-scrollbar-track {
      background: transparent;
    }

    .column-tasks::-webkit-scrollbar-thumb {
      background: var(--bs-border-color);
      border-radius: 3px;
    }

    .column-tasks::-webkit-scrollbar-thumb:hover {
      background: #7928ca;
    }

    .kanban-container::-webkit-scrollbar {
      height: 8px;
    }

    .kanban-container::-webkit-scrollbar-track {
      background: transparent;
    }

    .kanban-container::-webkit-scrollbar-thumb {
      background: var(--bs-border-color);
      border-radius: 4px;
    }

    .kanban-container::-webkit-scrollbar-thumb:hover {
      background: #7928ca;
    }
  </style>
</head>

<body>

 <?php include 'nav.php'; ?>


  <!--start main wrapper-->
  <main class="main-wrapper">
    <div class="main-content">
      <!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Kanban</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">Board</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">

					</div>
				</div>
				<!--end breadcrumb-->



        <div class="row g-3">
          <div class="col-auto">

          </div>
          <div class="col-auto flex-grow-1 overflow-auto">
            <div class="btn-group position-static">



            </div>
          </div>
          <div class="col-auto">
            <div class="d-flex align-items-center gap-2 justify-content-lg-end">
               <button class="btn btn-light px-4" onclick="loadKanbanBoard()"><i class="bi bi-arrow-clockwise me-2"></i>Refresh</button>
               <button class="btn btn-primary px-4" onclick="openColumnModal()"><i class="bi bi-plus-lg me-2"></i>Ny Kolonne</button>
            </div>
          </div>
        </div><!--end row-->

        <div class="card mt-3">
          <div class="card-body">
            <!-- Kanban Board Container -->
            <div class="kanban-container" id="kanban-container">
              <div class="kanban-loading">
                <div class="kanban-spinner"></div>
              </div>
            </div>
          </div>
        </div>


    </div>
  </main>
  <!--end main wrapper-->

  <!-- Task Modal -->
  <div class="kanban-modal-overlay" id="task-modal">
    <div class="kanban-modal-content">
      <div class="kanban-modal-header">
        <h5 class="kanban-modal-title" id="task-modal-title">Legg til Oppgave</h5>
        <button class="kanban-modal-close" onclick="closeTaskModal()">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <form id="task-form">
        <div class="kanban-modal-body">
          <div class="kanban-form-group">
            <label class="kanban-form-label" for="task-title">Tittel *</label>
            <input type="text" class="kanban-form-control" id="task-title" name="title" required
                   placeholder="Skriv inn oppgavetittel">
          </div>

          <div class="kanban-form-group">
            <label class="kanban-form-label" for="task-description">Beskrivelse</label>
            <textarea class="kanban-form-control" id="task-description" name="description"
                      placeholder="Skriv inn beskrivelse"></textarea>
          </div>

          <div class="row">
            <div class="col-md-6">
              <div class="kanban-form-group">
                <label class="kanban-form-label" for="task-priority">Prioritet</label>
                <select class="kanban-form-control" id="task-priority" name="priority">
                  <option value="low">Lav</option>
                  <option value="medium" selected>Medium</option>
                  <option value="high">Høy</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="kanban-form-group">
                <label class="kanban-form-label" for="task-due-date">Frist</label>
                <input type="date" class="kanban-form-control" id="task-due-date" name="due_date">
              </div>
            </div>
          </div>

          <div class="kanban-form-group">
            <label class="kanban-form-label" for="task-assigned">Tildelt til</label>
            <input type="text" class="kanban-form-control" id="task-assigned" name="assigned_to"
                   placeholder="Skriv inn navn">
          </div>

          <div class="kanban-form-group">
            <label class="kanban-form-label" for="task-labels">Etiketter</label>
            <input type="text" class="kanban-form-control" id="task-labels" name="labels"
                   placeholder="Skriv inn etiketter atskilt med komma (f.eks. design, ui, backend)">
          </div>
        </div>
        <div class="kanban-modal-footer">
          <button type="button" class="btn btn-light" onclick="closeTaskModal()">Avbryt</button>
          <button type="submit" class="btn btn-primary">Lagre</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Column Modal -->
  <div class="kanban-modal-overlay" id="column-modal">
    <div class="kanban-modal-content">
      <div class="kanban-modal-header">
        <h5 class="kanban-modal-title">Legg til Kolonne</h5>
        <button class="kanban-modal-close" onclick="closeColumnModal()">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <form id="column-form">
        <div class="kanban-modal-body">
          <div class="kanban-form-group">
            <label class="kanban-form-label" for="column-name">Kolonnenavn *</label>
            <input type="text" class="kanban-form-control" id="column-name" name="name" required
                   placeholder="Skriv inn kolonnenavn (f.eks. Backlog, In Progress)">
          </div>

          <div class="kanban-form-group">
            <label class="kanban-form-label" for="column-color">Farge</label>
            <input type="color" class="kanban-form-control" id="column-color" name="color"
                   value="#7928ca" style="height: 45px; padding: 5px;">
          </div>
        </div>
        <div class="kanban-modal-footer">
          <button type="button" class="btn btn-light" onclick="closeColumnModal()">Avbryt</button>
          <button type="submit" class="btn btn-primary">Opprett</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="kanban-modal-overlay" id="delete-modal">
    <div class="kanban-modal-content" style="max-width: 400px;">
      <div class="kanban-modal-header">
        <h5 class="kanban-modal-title">Bekreft Sletting</h5>
        <button class="kanban-modal-close" onclick="closeDeleteModal()">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="kanban-modal-body">
        <p id="delete-message" style="margin: 0; color: var(--bs-body-color);">
          Er du sikker på at du vil slette dette elementet?
        </p>
      </div>
      <div class="kanban-modal-footer">
        <button type="button" class="btn btn-light" onclick="closeDeleteModal()">Avbryt</button>
        <button type="button" class="btn btn-danger" onclick="confirmDelete()">Slett</button>
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div class="kanban-toast-container" id="kanban-toast-container"></div>


    <!--start overlay-->
    <div class="overlay btn-toggle"></div>
    <!--end overlay-->

     <?php include 'footer.php'; ?>

  <!--bootstrap js-->
  <script src="assets/js/bootstrap.bundle.min.js"></script>

  <!--plugins-->
  <script src="assets/js/jquery.min.js"></script>
  <!--plugins-->
  <script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
  <script src="assets/plugins/metismenu/metisMenu.min.js"></script>
  <script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js"></script>
  <script src='https://cdn.rawgit.com/t4t5/sweetalert/v0.2.0/lib/sweet-alert.min.js'></script>

  <!-- Kanban Board JavaScript -->
  <script>
    /**
     * Kanban Board JavaScript
     * Handles drag & drop, CRUD operations, and UI interactions
     */

    // API Base URL
    const API_BASE = 'api/kanban';

    // State
    let columns = [];
    let draggedTask = null;
    let draggedElement = null;

    // DOM Elements
    const kanbanContainer = document.getElementById('kanban-container');
    const taskModal = document.getElementById('task-modal');
    const columnModal = document.getElementById('column-modal');
    const deleteModal = document.getElementById('delete-modal');
    const toastContainer = document.getElementById('kanban-toast-container');

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
      loadKanbanBoard();
      setupEventListeners();
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
        throw error;
      }
    }

    async function loadKanbanBoard() {
      showLoading();
      try {
        columns = await fetchAPI('columns.php?board_id=1');
        renderBoard();
      } catch (error) {
        // Demo mode - load sample data
        columns = getSampleData();
        renderBoard();
        showToast('Demo-modus aktiv (API ikke tilkoblet)', 'warning');
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

        showToast('Oppgave opprettet!', 'success');
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

        showToast('Oppgave opprettet (demo-modus)', 'success');
        return newTask;
      }
    }

    async function updateTask(taskData) {
      try {
        await fetchAPI('tasks.php', {
          method: 'PUT',
          body: JSON.stringify(taskData)
        });

        for (const column of columns) {
          const taskIndex = column.tasks.findIndex(t => t.id == taskData.id);
          if (taskIndex > -1) {
            column.tasks[taskIndex] = { ...column.tasks[taskIndex], ...taskData };
            break;
          }
        }

        renderBoard();
        showToast('Oppgave oppdatert!', 'success');
      } catch (error) {
        for (const column of columns) {
          const taskIndex = column.tasks.findIndex(t => t.id == taskData.id);
          if (taskIndex > -1) {
            column.tasks[taskIndex] = { ...column.tasks[taskIndex], ...taskData };
            break;
          }
        }
        renderBoard();
        showToast('Oppgave oppdatert (demo-modus)', 'success');
      }
    }

    async function deleteTask(taskId) {
      try {
        await fetchAPI(`tasks.php?id=${taskId}`, { method: 'DELETE' });
      } catch (error) {
        // Continue in demo mode
      }

      for (const column of columns) {
        const taskIndex = column.tasks.findIndex(t => t.id == taskId);
        if (taskIndex > -1) {
          column.tasks.splice(taskIndex, 1);
          break;
        }
      }

      renderBoard();
      showToast('Oppgave slettet!', 'success');
    }

    async function moveTask(taskId, newColumnId, newPosition) {
      try {
        const result = await fetchAPI('reorder.php', {
          method: 'POST',
          body: JSON.stringify({
            task_id: taskId,
            new_column_id: newColumnId,
            new_position: newPosition
          })
        });
        console.log('Reorder result:', result);
        if (result.success) {
          showToast('Oppgave flyttet!', 'success');
        }
      } catch (error) {
        console.error('Reorder failed:', error);
        showToast('Kunne ikke flytte oppgaven', 'error');
        // Reload board to get correct state
        loadKanbanBoard();
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
        showToast('Kolonne opprettet!', 'success');
        return newColumn;
      } catch (error) {
        const newColumn = {
          id: Date.now(),
          ...columnData,
          position: columns.length,
          tasks: []
        };

        columns.push(newColumn);
        renderBoard();
        showToast('Kolonne opprettet (demo-modus)', 'success');
        return newColumn;
      }
    }

    async function deleteColumn(columnId) {
      try {
        await fetchAPI(`columns.php?id=${columnId}`, { method: 'DELETE' });
      } catch (error) {
        // Continue in demo mode
      }

      columns = columns.filter(c => c.id != columnId);
      renderBoard();
      showToast('Kolonne slettet!', 'success');
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
      addColumnBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Legg til Kolonne';
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
            <span class="color-indicator" style="background-color: ${column.color || '#7928ca'};"></span>
            ${escapeHtml(column.name)}
            <span class="column-badge">${column.tasks.length}</span>
          </div>
          <div class="column-actions">
            <button class="column-action-btn" onclick="openTaskModal(${column.id})" title="Legg til oppgave">
              <i class="bi bi-plus-lg"></i>
            </button>
            <button class="column-action-btn" onclick="confirmDeleteColumn(${column.id})" title="Slett kolonne">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
        <div class="column-tasks" data-column-id="${column.id}">
          ${tasksHtml}
        </div>
        <button class="add-task-btn" onclick="openTaskModal(${column.id})">
          <i class="bi bi-plus"></i> Legg til Oppgave
        </button>
      `;

      return columnEl;
    }

    function createTaskHtml(task) {
      const labelsHtml = (task.labels || []).map(label =>
        `<span class="task-label label-${label.toLowerCase()}">${escapeHtml(label)}</span>`
      ).join('');

      const priorityClass = `priority-${task.priority || 'medium'}`;
      const priorityLabels = { high: 'Høy', medium: 'Medium', low: 'Lav' };
      const priorityText = priorityLabels[task.priority] || 'Medium';

      let dueDateHtml = '';
      if (task.due_date) {
        const dueDate = new Date(task.due_date);
        dueDateHtml = `
          <span class="task-due-date">
            <i class="bi bi-calendar3"></i>
            ${dueDate.toLocaleDateString('nb-NO')}
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
              <button class="task-action-btn" onclick="editTask(${task.id})" title="Rediger">
                <i class="bi bi-pencil"></i>
              </button>
              <button class="task-action-btn delete" onclick="confirmDeleteTask(${task.id})" title="Slett">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>
      `;
    }

    function showLoading() {
      kanbanContainer.innerHTML = `
        <div class="kanban-loading">
          <div class="kanban-spinner"></div>
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
      if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.classList.remove('drag-over');
      }
    }

    function handleDrop(e) {
      e.preventDefault();
      e.currentTarget.classList.remove('drag-over');

      const newColumnId = parseInt(e.currentTarget.dataset.columnId);
      const taskId = parseInt(e.dataTransfer.getData('text/plain'));

      if (!taskId || !newColumnId) {
        console.error('Invalid drop data');
        return;
      }

      // Find the position where the task was dropped
      const taskCards = Array.from(e.currentTarget.querySelectorAll('.task-card:not(.dragging)'));
      let newPosition = taskCards.length; // Default to end

      // Find insertion point based on current DOM order
      const draggingCard = document.querySelector('.task-card.dragging');
      if (draggingCard) {
        const allCards = Array.from(e.currentTarget.querySelectorAll('.task-card'));
        newPosition = allCards.indexOf(draggingCard);
        if (newPosition === -1) newPosition = taskCards.length;
      }

      // Update local state
      let movedTask = null;
      let oldColumnId = null;

      for (const column of columns) {
        const taskIndex = column.tasks.findIndex(t => t.id == taskId);
        if (taskIndex > -1) {
          oldColumnId = column.id;
          movedTask = column.tasks.splice(taskIndex, 1)[0];
          break;
        }
      }

      if (movedTask) {
        const newColumn = columns.find(c => c.id == newColumnId);
        if (newColumn) {
          movedTask.column_id = newColumnId;
          movedTask.position = newPosition;
          newColumn.tasks.splice(newPosition, 0, movedTask);

          // Recalculate all positions
          newColumn.tasks.forEach((task, idx) => {
            task.position = idx;
          });
        }
      }

      // Call API to persist
      moveTask(taskId, newColumnId, newPosition);

      // Re-render board
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
        modalTitle.textContent = 'Rediger Oppgave';
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
        modalTitle.textContent = 'Legg til Oppgave';
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
      document.getElementById('delete-message').textContent = 'Er du sikker på at du vil slette denne oppgaven? Denne handlingen kan ikke angres.';
      deleteModal.classList.add('active');
    }

    function confirmDeleteColumn(columnId) {
      const column = columns.find(c => c.id == columnId);
      deleteTarget = { type: 'column', id: columnId };
      document.getElementById('delete-message').textContent = `Er du sikker på at du vil slette kolonnen "${column.name}" og alle dens oppgaver? Denne handlingen kan ikke angres.`;
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
      document.getElementById('task-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = new FormData(e.target);
        const labelsString = formData.get('labels') || '';
        const labels = labelsString.split(',').map(l => l.trim()).filter(l => l);

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

      document.querySelectorAll('.kanban-modal-overlay').forEach(modal => {
        modal.addEventListener('click', (e) => {
          if (e.target === modal) {
            modal.classList.remove('active');
          }
        });
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          document.querySelectorAll('.kanban-modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
          });
        }
      });
    }

    // ============================================
    // Toast Notifications
    // ============================================

    function showToast(message, type = 'info') {
      const toast = document.createElement('div');
      toast.className = `kanban-toast ${type}`;
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

    // Sample data for demo mode
    function getSampleData() {
      return [
        {
          id: 1,
          name: 'Å Gjøre',
          position: 0,
          color: '#6c757d',
          tasks: [
            {
              id: 1,
              title: 'Design Hjemmeside',
              description: 'Lage wireframes og mockups for den nye hjemmesiden',
              priority: 'high',
              position: 0,
              labels: ['design', 'ui']
            },
            {
              id: 2,
              title: 'Oppsett Database',
              description: 'Konfigurere MySQL og opprette tabeller',
              priority: 'medium',
              position: 1,
              labels: ['backend']
            }
          ]
        },
        {
          id: 2,
          name: 'Pågår',
          position: 1,
          color: '#0d6efd',
          tasks: [
            {
              id: 3,
              title: 'Implementer Autentisering',
              description: 'Legge til innlogging og registreringsfunksjonalitet',
              priority: 'high',
              position: 0,
              labels: ['backend', 'security']
            },
            {
              id: 4,
              title: 'Opprett API Endepunkter',
              description: 'Bygge RESTful API for oppgavehåndtering',
              priority: 'medium',
              position: 1,
              labels: ['backend', 'api']
            }
          ]
        },
        {
          id: 3,
          name: 'Gjennomgang',
          position: 2,
          color: '#ffc107',
          tasks: [
            {
              id: 5,
              title: 'Kode Gjennomgang',
              description: 'Gjennomgå pull requests fra teammedlemmer',
              priority: 'low',
              position: 0,
              labels: ['review']
            }
          ]
        },
        {
          id: 4,
          name: 'Ferdig',
          position: 3,
          color: '#198754',
          tasks: [
            {
              id: 6,
              title: 'Prosjekt Oppsett',
              description: 'Initialisere prosjektstruktur og avhengigheter',
              priority: 'low',
              position: 0,
              labels: ['setup']
            }
          ]
        }
      ];
    }
  </script>

</body>

</html>
