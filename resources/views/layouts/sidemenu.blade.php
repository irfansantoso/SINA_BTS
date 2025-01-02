<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  <div class="app-brand demo">
    <a href="{{ route('dashboard') }}" class="app-brand-link">
      <span class="app-brand-logo">
        <img
          width="62"
          height="62"
          src="{{asset('admin/assets/img/branding/logo-sina.png')}}"
          class=""
        />
      </span>
      <span class="app-brand-text demo menu-text fw-bold">SINA-BTJ</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
      <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">    

    <!-- Apps & Pages -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Homepage</span>
    </li>
    <li class="{{ request()->is('dashboard') ? 'menu-item active' : 'menu-item' }}">
      <a href="{{ route('dashboard') }}" class="menu-link">
        <i class="menu-icon tf-icons ti ti-dashboard"></i>
        <div data-i18n="Dashboard">Dashboard</div>
      </a>
    </li>
    <!-- Master -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Menu</span>
    </li>    

    <li class="{{ request()->is('users','siteSina','accountTypeSina','currencySina') ? 'menu-item active open' : 'menu-item' }}">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-category"></i>
        <div data-i18n="Master">Master</div>
      </a>
      <ul class="menu-sub">
        <li class="{{ request()->is('users') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('users') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-users"></i>
            <div data-i18n="Users">Users</div>
          </a>
        </li>
        <li class="{{ request()->is('siteSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('siteSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-atom-2"></i>
            <div data-i18n="Site">Site</div>
          </a>
        </li>
        <li class="{{ request()->is('accountTypeSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('accountTypeSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-atom-2"></i>
            <div data-i18n="Account Type">Account Type</div>
          </a>
        </li>
        <li class="{{ request()->is('currencySina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('currencySina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-atom-2"></i>
            <div data-i18n="Currency">Currency</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="{{ request()->is('accountingPeriodSina') ? 'menu-item active open' : 'menu-item' }}">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-abacus"></i>
        <div data-i18n="Accounting Periode">Accounting Periode</div>
      </a>
      <ul class="menu-sub">
        <li class="{{ request()->is('accountingPeriodSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('accountingPeriodSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-users"></i>
            <div data-i18n="Accounting Periode">Accounting Periode</div>
          </a>
        </li>
      </ul>
    </li>

    <li class="{{ request()->is('accountListSina','divisionListSina','costListSina','journalGroupSina','journalSourceCodeSina') ? 'menu-item active open' : 'menu-item' }}">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-table"></i>
        <div data-i18n="Tables">Tables</div>
      </a>
      <ul class="menu-sub">
        <li class="{{ request()->is('accountListSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('accountListSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-chart-pie-2"></i>
            <div data-i18n="Account List">Account List</div>
          </a>
        </li>
        <li class="{{ request()->is('divisionListSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('divisionListSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-atom-2"></i>
            <div data-i18n="Division List">Division List</div>
          </a>
        </li>
        <li class="{{ request()->is('costListSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('costListSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-atom-2"></i>
            <div data-i18n="Cost List">Cost List</div>
          </a>
        </li>
        <li class="{{ request()->is('journalGroupSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('journalGroupSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-atom-2"></i>
            <div data-i18n="Journal Group">Journal Group</div>
          </a>
        </li>
        <li class="{{ request()->is('journalSourceCodeSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('journalSourceCodeSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-atom-2"></i>
            <div data-i18n="Journal Source Code">Journal Source Code</div>
          </a>
        </li>
      </ul>
    </li>

    <!-- Forms Inputs -->
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Forms &amp; Report</span>
    </li>
    <!-- Forms -->
    <li class="{{ request()->is('journalSina') ? 'menu-item active open' : 'menu-item' }}">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-table"></i>
        <div data-i18n="Journal">Journal</div>
      </a>
      <ul class="menu-sub">
        <li class="{{ request()->is('journalSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('journalSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-chart-pie-2"></i>
            <div data-i18n="Journal">Journal</div>
          </a>
        </li>        
      </ul>
    </li>
    <!-- Forms -->
    <li class="{{ request()->is('rptGenLedSina') ? 'menu-item active open' : 'menu-item' }}">
      <a href="javascript:void(0)" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons ti ti-table"></i>
        <div data-i18n="Report">Report</div>
      </a>
      <ul class="menu-sub">
        <li class="{{ request()->is('rptGenLedSina') ? 'menu-item active' : 'menu-item' }}">
          <a href="{{ route('rptGenLedSina') }}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-chart-pie-2"></i>
            <div data-i18n="General Ledger">General Ledger</div>
          </a>
        </li>        
      </ul>
    </li> 

  </ul>
</aside>