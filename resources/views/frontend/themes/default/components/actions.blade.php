<div x-data="{ in_app: {{ $in_app ? "true" : "false" }} }">
    <div x-show.transition.in="in_app" class="app-action mt-4 px-8" style="display: none">
        @if (count($emails) > 0 && $in_app)
            <div class="lg:max-w-72 lg:mx-auto">
                <a href="{{ Util::localizeRoute("mailbox") }}" class="block appearance-none w-full rounded-md my-5 py-3 px-5 bg-white bg-opacity-25 dark:bg-opacity-25 dark:bg-gray-800 text-white text-sm cursor-pointer focus:outline-none hover:bg-opacity-50">
                    <i class="fas fa-angle-double-left"></i>
                    <span class="ml-2">{{ __("Get back to MailBox") }}</span>
                </a>
            </div>
        @endif

        <form wire:submit.prevent="create" class="lg:max-w-72 lg:mx-auto" method="post">
            @if (config("app.settings.captcha") == "hcaptcha" || config("app.settings.captcha") == "recaptcha2")
                <x-captcha field="captcha" />
            @endif

            <input class="block appearance-none w-full border-0 rounded-md py-4 px-5 bg-white text-white bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 focus:outline-none placeholder-white placeholder-opacity-50 dark:placeholder-gray-400" type="text" name="user" id="user" wire:model.defer="user" placeholder="{{ __("Enter Username") }}" />
            <div class="divider mt-5"></div>
            <div class="relative">
                <x-dropdown width="full">
                    <x-slot name="trigger">
                        <input x-ref="domain" type="text" class="block appearance-none w-full border-0 bg-white text-white py-4 px-5 pr-8 bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 rounded-md cursor-pointer focus:outline-none select-none placeholder-white placeholder-opacity-50 dark:placeholder-gray-400" placeholder="{{ __("Select Domain") }}" name="domain" id="domain" wire:model="domain" readonly />
                    </x-slot>
                    <x-slot name="content">
                        @foreach ($domains as $domain)
                            <a x-on:click="
                                $refs.domain.value = '{{ $domain }}'
                                $wire.setDomain('{{ $domain }}')
                            " class="block px-4 py-2 text-sm leading-5 text-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 transition duration-150 ease-in-out">{{ $domain }}</a>
                        @endforeach

                        @foreach ($memberDomains as $domain)
                            <a class="cursor-not-allowed flex justify-between px-4 py-2 text-sm leading-5 text-gray-500 dark:text-gray-400">
                                <span>{{ $domain }}</span>
                                <span class="text-xs px-2 py-1 rounded-md bg-gray-900 dark:bg-gray-700 text-white dark:text-gray-300">{{ __("Member Only") }}</span>
                            </a>
                        @endforeach
                    </x-slot>
                </x-dropdown>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-5 text-white dark:text-gray-300">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                </div>
            </div>
            <div class="divider mt-5"></div>
            <input id="create" class="block appearance-none w-full rounded-md py-4 px-5 bg-teal-500 dark:bg-teal-600 text-white cursor-pointer focus:outline-none" style="background-color: {{ config("app.settings.colors.secondary") }}" type="submit" value="{{ __("Create") }}" />
            <div class="divider my-8 flex justify-center">
                <div class="border-t-2 w-2/3 border-white border-opacity-25 dark:border-gray-700"></div>
            </div>
        </form>
        <form wire:submit.prevent="random" class="lg:max-w-72 lg:mx-auto" method="post">
            <input id="random" class="block appearance-none w-full rounded-md py-4 px-5 bg-yellow-500 dark:bg-yellow-600 text-white cursor-pointer focus:outline-none" style="background-color: {{ config("app.settings.colors.tertiary") }}" type="submit" value="{{ __("Random") }}" />
        </form>
        @if (! $in_app)
            <div class="lg:max-w-72 lg:mx-auto">
                <button x-on:click="in_app = false" class="block appearance-none w-full rounded-md my-5 py-2 px-5 bg-white bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 text-white text-sm cursor-pointer focus:outline-none hover:bg-opacity-50">{{ __("Cancel") }}</button>
            </div>
        @endif
    </div>
    <div x-show.transition.in="!in_app" class="in-app-actions mt-4 px-8" style="display: none">
        <form class="lg:max-w-72 lg:mx-auto" action="#" method="post">
            <div class="relative">
                <x-dropdown align="top" width="full">
                    <x-slot name="trigger">
                        <div class="block appearance-none w-full bg-white text-white py-4 px-5 pr-8 bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 rounded-md cursor-pointer focus:outline-none select-none" id="email_id">
                            {{ $email ?: __("Generating Email...") }}
                        </div>
                    </x-slot>
                    <x-slot name="content">
                        @foreach ($emails as $email)
                            <x-dropdown-link href="{{ route('switch', $email) }}">
                                {{ $email }}
                            </x-dropdown-link>
                        @endforeach
                    </x-slot>
                </x-dropdown>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-white dark:text-gray-300">
                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" /></svg>
                </div>
            </div>
        </form>
        <div class="divider mt-5"></div>
        <div class="grid grid-cols-4 lg:grid-cols-2 gap-2 lg:gap-6 lg:max-w-72 lg:mx-auto">
            <div class="btn_copy bg-white bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 text-white rounded-md py-5 lg:py-10 text-center hover:bg-opacity-25 hover:dark:bg-opacity-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="text-xl lg:text-3xl mx-auto">
                    <i class="far fa-copy"></i>
                </div>
                <div class="text-xs lg:text-base pt-5">{{ __("Copy") }}</div>
            </div>
            <div onclick="document.getElementById('refresh').classList.remove('pause-spinner')" wire:click="$dispatch('fetchMessages')" class="bg-white bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 text-white rounded-md py-5 lg:py-10 text-center hover:bg-opacity-25 hover:dark:bg-opacity-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="text-xl lg:text-3xl mx-auto">
                    <i id="refresh" class="fas fa-sync-alt fa-spin"></i>
                </div>
                <div class="text-xs lg:text-base pt-5">{{ __("Refresh") }}</div>
            </div>
            <div x-on:click="in_app = true" class="bg-white bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 text-white rounded-md py-5 lg:py-10 text-center hover:bg-opacity-25 hover:dark:bg-opacity-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="text-xl lg:text-3xl mx-auto">
                    <i class="far fa-plus-square"></i>
                </div>
                <div class="text-xs lg:text-base pt-5">{{ __("New") }}</div>
            </div>
            <div wire:click="deleteEmail" class="bg-white bg-opacity-10 dark:bg-opacity-20 dark:bg-gray-800 text-white rounded-md py-5 lg:py-10 text-center hover:bg-opacity-25 hover:dark:bg-opacity-50 dark:hover:bg-gray-700 cursor-pointer">
                <div class="text-xl lg:text-3xl mx-auto">
                    <i class="far fa-trash-alt"></i>
                </div>
                <div class="text-xs lg:text-base pt-5">{{ __("Delete") }}</div>
            </div>
        </div>
    </div>

    @if (config("app.settings.captcha") == "recaptcha3")
        <script src="https://www.google.com/recaptcha/api.js?render={{ config("app.settings.recaptcha3.site_key") }}"></script>
        <script>
            const handle = (e) => {
                e.preventDefault();
                grecaptcha.ready(function () {
                    grecaptcha.execute('{{ config("app.settings.recaptcha3.site_key") }}', { action: 'submit' }).then(function (token) {
                        Livewire.dispatch('checkReCaptcha3', {
                            token,
                            action: e.target.id,
                        });
                    });
                });
            };
            document.getElementById('create').addEventListener('click', handle);
            document.getElementById('random').addEventListener('click', handle);
        </script>
    @endif
</div>
