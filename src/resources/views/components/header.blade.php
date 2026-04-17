<header class="header">
    <div class="header__logo">
        <a href="/">
            <img src="/img/logo.png" alt="COACHTECH">
        </a>
    </div>

        <nav class="header__nav">
            <ul class="header__nav-list">
                @if(Auth::guard('admin')->check())
                    <li class="header__nav-item"><a href="{{ route('admin.attendance.list') }}"
                            class="header__nav-link">勤怠一覧</a></li>
                    <li class="header__nav-item"><a href="{{ route('admin.staff.list') }}"
                            class="header__nav-link">スタッフ一覧</a></li>
                    <li class="header__nav-item"><a href="{{ route('request.list') }}" class="header__nav-link">申請一覧</a></li>
                    <li class="header__nav-item">
                        <form action="{{ route('admin.logout') }}" method="post" style="display: inline;">
                            @csrf
                            <button type="submit" class="header__nav-link logout-button">ログアウト</button>
                        </form>
                    </li>
                @elseif(Auth::guard('web')->check())
                    <li class="header__nav-item"><a href="{{ route('attendance.index') }}" class="header__nav-link">勤怠</a>
                    </li>
                    <li class="header__nav-item"><a href="{{ route('attendance.list') }}" class="header__nav-link">勤怠一覧</a>
                    </li>
                    <li class="header__nav-item"><a href="{{ route('request.list') }}" class="header__nav-link">申請</a></li>
                    <li class="header__nav-item">
                        <form action="{{ route('logout') }}" method="post" style="display: inline;">
                            @csrf
                            <button type="submit" class="header__nav-link logout-button">ログアウト</button>
                        </form>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</header>