<style>
    .sidebar-link.disabled {
        pointer-events: none;
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<aside class="left-sidebar" data-sidebarbg="skin5">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav" class="p-t-30">
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'dashboard_vendedor.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-view-dashboard"></i>
                        <span class="hide-menu">Panel de Control</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'frontend_create_vendedor.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-pencil"></i>
                        <span class="hide-menu">Agregar Propiedades</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'frontend_update_vendedor.php'; ?>" aria-expanded="false">
                        <i class="fas fa-edit"></i>
                        <span class="hide-menu">Modificar Propiedades</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'frontend_delete_vendedor.php'; ?>" aria-expanded="false">
                        <i class="fas fa-times-circle"></i>
                        <span class="hide-menu">Eliminar Propiedades</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'chats_seller.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-message-text"></i>
                        <span class="hide-menu">Lista de Chats</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'frontend_seller_sales.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-message-text"></i>
                        <span class="hide-menu">Ventas</span>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>