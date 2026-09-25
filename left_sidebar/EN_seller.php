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
                        href="<?php echo $subs_vencida ? '#' : 'EN_dashboard_vendedor.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-view-dashboard"></i>
                        <span class="hide-menu">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'EN_frontend_create_vendedor.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-pencil"></i>
                        <span class="hide-menu">Add Properties</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'EN_frontend_update_vendedor.php'; ?>" aria-expanded="false">
                        <i class="fas fa-edit"></i>
                        <span class="hide-menu">Edit Properties</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'EN_frontend_delete_vendedor.php'; ?>" aria-expanded="false">
                        <i class="fas fa-times-circle"></i>
                        <span class="hide-menu">Delete Properties</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'EN_chats_seller.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-message-text"></i>
                        <span class="hide-menu">Chat List</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a class="sidebar-link waves-effect waves-dark sidebar-link <?php if ($subs_vencida) echo 'disabled'; ?>"
                        href="<?php echo $subs_vencida ? '#' : 'EN_frontend_seller_sales.php'; ?>" aria-expanded="false">
                        <i class="mdi mdi-message-text"></i>
                        <span class="hide-menu">Sales</span>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>