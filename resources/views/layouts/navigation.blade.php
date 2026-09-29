<nav x-data="{ open: false }" class="bg-cream border-b border-charcoal/10">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="font-serif text-2xl font-bold" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Beranda') }}
                    </x-nav-link>

                    @auth
                        <x-nav-link :href="route('reservations.index')" :active="request()->routeIs('reservations.*')">
                            {{ __('Reservasi Saya') }}
                        </x-nav-link>

                        @can('lihat-reservasi-aktif')
                            <x-nav-link :href="route('staff.reservations')" :active="request()->routeIs('staff.*')">
                                {{ __('Reservasi Aktif') }}
                            </x-nav-link>
                        @endcan

                        @role('admin')
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                                {{ __('Admin') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.tables.index')" :active="request()->routeIs('admin.tables.*')">
                                {{ __('Meja') }}
                            </x-nav-link>
                            <x-nav-link :href="route('admin.reconciliation')" :active="request()->routeIs('admin.reconciliation')">
                                {{ __('Closing Kasir') }}
                            </x-nav-link>
                        @endrole
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-charcoal/10 text-sm leading-4 font-medium rounded-md text-charcoal/70 hover:text-charcoal focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profil') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Keluar') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-charcoal/60 hover:text-charcoal hover:bg-cream-dark focus:outline-none focus:bg-cream-dark focus:text-charcoal transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Beranda') }}
            </x-responsive-nav-link>

            @auth
                <x-responsive-nav-link :href="route('reservations.index')" :active="request()->routeIs('reservations.*')">
                    {{ __('Reservasi Saya') }}
                </x-responsive-nav-link>

                @can('lihat-reservasi-aktif')
                    <x-responsive-nav-link :href="route('staff.reservations')" :active="request()->routeIs('staff.*')">
                        {{ __('Reservasi Aktif') }}
                    </x-responsive-nav-link>
                @endcan

                @role('admin')
                    <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                        {{ __('Admin') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.tables.index')" :active="request()->routeIs('admin.tables.*')">
                        {{ __('Meja') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.reconciliation')" :active="request()->routeIs('admin.reconciliation')">
                        {{ __('Closing Kasir') }}
                    </x-responsive-nav-link>
                @endrole
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-charcoal/10">
            <div class="px-4">
                <div class="font-medium text-base text-charcoal">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-charcoal/60">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Keluar') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
