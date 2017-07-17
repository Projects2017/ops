<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
               <span class="logo-mobile">
                   <object data="new_css/img/logo.svg" type="image/svg+xml"></object>
           </span>
        </div>

        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MAIN NAVIGATION</li>
            <li class="active treeview menu-open">
                <a href="/selectvendor.php">
                    <i class="icon icon-gauge"></i> <span>HOME</span>
               </a>
           </li>
            <li>
                <a href="/admin/users.php">
                    <i class="icon icon-users-2"></i>  <span>DEALERS</span>
                    <span class="pull-right-container"><span class="label label-primary pull-right">4</span>
                </a>
             </li>
            <li>
                <a href="/admin/vendors.php">
                    <i class="icon icon-edit-2"></i> <span>VENDORS / FORMS</span><span class="pull-right-container"><small class="label pull-right bg-green">new</small></span>
                </a>
            </li>
            <li>
                <a href="/summary.php">
                    <i class="icon icon-truck"></i>
                    <span>ORDERS</span>
                 </a>
           </li>
            <li>
                <a href="/admin/orders-summary.php">
                    <i class="icon icon-clipboard-2"></i>
                    <span>SUMMARY</span>
                </a>
            </li>
            <li>
                <a href="/admin/rate-of-sale.php">
                    <i class="icon icon-chart-outline"></i> <span>RATE OF SALES</span>
                </a>
             </li>
            <li>
                <a href="/admin/announce-admin.php">
                    <i class="fa fa-table"></i> <span>ANNOUNCEMENT</span>
              </a>
           </li>
            <li>
                <a href="/admin/shipdiff.php">
                    <i class="fa fa-calendar"></i> <span>SHIPPING SPEED</span>
               </a>
            </li>
            <li>
                <a href="/admin/ch_summary.php">
                <i class="fa fa-envelope"></i> <span>COMMERCEHUB</span>
              </a>
            </li>
            <li>
                <a href="/form.php">
                    <i class="fa fa-folder"></i> <span>CLAIMS</span>
              </a>
              </li>
            <li>
                <a href="/shipping/shipping.php">
                    <i class="fa fa-share"></i> <span>SHIPPING SYSTEM</span>
                </a>
            </li>
            <li><a href="/wiki/"><i class="fa fa-book"></i> <span>WIKI</span></a></li>
            <li><a href="/users.php"><i class="fa fa-book"></i> <span>Sales Report</span></a></li>
            <li><a href="<?php if ($bigboardint) : ?>/leaderboard/<?php else: ?>http://www.boxdropbigboard.com/<?php endif; ?>"><i class="fa fa-book"></i> <span>BIG BOARD</span></a></li>
        </ul>
    </section>
    <!-- /.sidebar -->
</aside>