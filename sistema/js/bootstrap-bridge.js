function applyBootstrapClasses(selector, classes) {
    document.querySelectorAll(selector).forEach((element) => {
        element.classList.add(...classes);
    });
}

function enhanceLegacyButtons() {
    document.querySelectorAll('.btn_new').forEach((element) => {
        element.classList.add('btn', 'btn-primary', 'd-inline-flex', 'align-items-center', 'justify-content-center', 'gap-2');
    });

    document.querySelectorAll('.btn_pdf').forEach((element) => {
        element.classList.add('btn', 'btn-outline-danger');
    });

    document.querySelectorAll('.btn_excel').forEach((element) => {
        element.classList.add('btn', 'btn-outline-success');
    });

    document.querySelectorAll('.btn_save, .btn_save_1').forEach((element) => {
        element.classList.add('btn', 'btn-primary');
    });

    document.querySelectorAll('.link_delete_1').forEach((element) => {
        element.classList.add('btn', 'btn-outline-secondary');
    });

    document.querySelectorAll('.btn-delete, .btn_anular').forEach((element) => {
        element.classList.add('btn', 'btn-outline-danger', 'btn-sm');
    });

    document.querySelectorAll('.btn-edit').forEach((element) => {
        element.classList.add('btn', 'btn-outline-primary', 'btn-sm');
    });

    document.querySelectorAll('.btn_ok').forEach((element) => {
        element.classList.add('btn', 'btn-outline-success', 'btn-sm');
    });

    document.querySelectorAll('.btn_busventa').forEach((element) => {
        element.classList.add('btn', 'btn-outline-info', 'btn-sm');
    });
}

function enhanceLegacyForms() {
    document.querySelectorAll('form').forEach((form) => {
        form.classList.add('needs-bootstrap-bridge');
    });

    document.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):not(.form-control), textarea:not(.form-control)').forEach((field) => {
        field.classList.add('form-control');
    });

    document.querySelectorAll('select:not(.form-select)').forEach((field) => {
        field.classList.add('form-select');
    });

    document.querySelectorAll('label').forEach((label) => {
        label.classList.add('form-label', 'fw-semibold');
    });

    document.querySelectorAll('.form_search').forEach((formSearch) => {
        formSearch.classList.add('row', 'g-3', 'align-items-end', 'w-100', 'm-0');
        Array.from(formSearch.children).forEach((child) => {
            if (child.tagName === 'DIV') {
                child.classList.add('col-12', 'col-sm-6', 'col-lg-4', 'col-xl-3');
            } else if (child.matches('button, .btn, a')) {
                child.classList.add('col-12', 'col-sm-auto');
            }
        });
    });
}

function enhanceLegacyTables() {
    document.querySelectorAll('.containerTable').forEach((wrapper) => {
        wrapper.classList.add('table-responsive', 'bg-white', 'rounded-4', 'shadow-sm', 'border', 'p-2');
    });

    document.querySelectorAll('.containerTable table, #tablaPMV, #tablaVentas, #tablaGastos, #tablaArticulos, #tablaCaja, #tablaCajaDia, #tablaClientes, #tablaUsuarios, #tablaPrestamos, #tablaProveedores').forEach((table) => {
        table.classList.add('table', 'table-hover', 'align-middle', 'mb-0');
    });
}

function enhanceLegacyContainers() {
    const container = document.getElementById('container');
    if (container) {
        container.classList.add('container-fluid');
    }

    applyBootstrapClasses('.title_container', ['d-flex', 'flex-wrap', 'justify-content-between', 'align-items-center', 'gap-3', 'mb-3']);
    applyBootstrapClasses('.header_container', ['bg-white', 'rounded-4', 'shadow-sm', 'border', 'p-3', 'mb-4']);
    applyBootstrapClasses('.left_section', ['d-flex', 'flex-column', 'gap-3', 'w-100']);
    applyBootstrapClasses('.right_section', ['d-flex', 'flex-wrap', 'justify-content-start', 'justify-content-lg-end', 'align-items-end', 'gap-2', 'w-100', 'mt-3', 'mt-lg-0', 'ps-0']);
    applyBootstrapClasses('.form_imprimir', ['d-flex', 'flex-wrap', 'gap-2', 'mb-0']);
    applyBootstrapClasses('.botonConfirmarCancelar', ['d-flex', 'flex-wrap', 'justify-content-end', 'gap-2']);
    applyBootstrapClasses('.botonAbrirCerrarCaja', ['d-flex', 'flex-wrap', 'gap-2']);
    applyBootstrapClasses('.info_caja', ['card', 'shadow-sm', 'border-0']);
    applyBootstrapClasses('.chart-container', ['card', 'shadow-sm', 'border-0', 'p-3']);
}

function enhanceLegacyDashboards() {
    document.querySelectorAll('.dashboard').forEach((dashboard) => {
        dashboard.classList.add('row', 'g-3', 'mb-4');
        dashboard.querySelectorAll(':scope > a').forEach((link) => {
            link.classList.add('col-12', 'col-sm-6', 'col-xl-4', 'text-decoration-none');
            if (!link.querySelector('.dashboard-card-inner')) {
                const content = document.createElement('div');
                content.className = 'dashboard-card-inner card h-100 border-0 shadow-sm';
                while (link.firstChild) {
                    content.appendChild(link.firstChild);
                }
                link.appendChild(content);
            }
        });
    });
}

function enhanceLegacyActionMenus() {
    document.querySelectorAll('.action-menu').forEach((menu) => {
        menu.classList.add('dropdown');

        const trigger = menu.querySelector(':scope > span');
        if (trigger && trigger.tagName !== 'BUTTON') {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-outline-secondary btn-sm dropdown-toggle';
            button.setAttribute('data-bs-toggle', 'dropdown');
            button.setAttribute('aria-expanded', 'false');
            button.textContent = trigger.textContent.trim() || 'Acciones';
            trigger.replaceWith(button);
        }

        const content = menu.querySelector('.action-menu-content');
        if (content) {
            content.classList.add('dropdown-menu', 'dropdown-menu-end', 'shadow', 'border-0');
            content.querySelectorAll('button, a').forEach((action) => {
                action.classList.add('dropdown-item');
            });
        }
    });
}

function enhanceLegacyModals() {
    document.querySelectorAll('.formulario').forEach((modal) => {
        if (modal.dataset.bootstrapReady === 'true') {
            return;
        }

        modal.classList.add('modal', 'fade');
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-hidden', 'true');

        const contentSource = modal.firstElementChild;
        if (!contentSource) {
            return;
        }

        const dialog = document.createElement('div');
        dialog.className = 'modal-dialog modal-dialog-centered modal-dialog-scrollable';

        const content = document.createElement('div');
        content.className = 'modal-content border-0 shadow';

        contentSource.classList.add('border-0', 'shadow-none', 'm-0', 'bg-transparent', 'p-4');
        content.appendChild(contentSource);
        dialog.appendChild(content);
        modal.appendChild(dialog);

        modal.dataset.bootstrapReady = 'true';
    });
}

function enhanceLegacySearchResults() {
    document.querySelectorAll('.resultados-container').forEach((container) => {
        container.classList.add('list-group', 'shadow-sm', 'rounded-4', 'overflow-auto');
    });

    document.querySelectorAll('.select-btn').forEach((item) => {
        item.classList.add('list-group-item', 'list-group-item-action');
    });
}

function initializeBootstrapBridge() {
    enhanceLegacyButtons();
    enhanceLegacyForms();
    enhanceLegacyTables();
    enhanceLegacyContainers();
    enhanceLegacyDashboards();
    enhanceLegacyActionMenus();
    enhanceLegacyModals();
    enhanceLegacySearchResults();
}

window.showLegacyModal = function showLegacyModal(id) {
    const modalElement = document.getElementById(id);
    if (!modalElement) {
        return;
    }

    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
        return;
    }

    modalElement.style.display = 'block';
    modalElement.classList.add('show');
};

window.hideLegacyModal = function hideLegacyModal(id) {
    const modalElement = document.getElementById(id);
    if (!modalElement) {
        return;
    }

    if (window.bootstrap && bootstrap.Modal) {
        const instance = bootstrap.Modal.getInstance(modalElement) || bootstrap.Modal.getOrCreateInstance(modalElement);
        instance.hide();
        return;
    }

    modalElement.style.display = 'none';
    modalElement.classList.remove('show');
};

document.addEventListener('DOMContentLoaded', initializeBootstrapBridge);
