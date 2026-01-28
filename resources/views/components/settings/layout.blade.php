@use('App\Models\Team\Team')

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('user-password.edit')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navlist.item :href="route('two-factor.show')" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navlist.item>
            @endif
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
            <flux:navlist.item :href="route('language.edit')" wire:navigate>{{ __('Language') }}</flux:navlist.item>

            @php($team = Team::currentTeam())
            <flux:separator class="my-2" />
            <flux:navlist.group expandable heading="{{ __('Team Settings') }}" class="mt-2">
                <flux:navlist.item :href="route('team.edit')" wire:navigate>{{ __('Team Information') }}</flux:navlist.item>
                @can('create', $team)
                    <flux:navlist.item :href="route('team.create')" wire:navigate>{{ __('Create New Team') }}</flux:navlist.item>
                @endcan
                @can('addTeamMember', $team)
                    <flux:navlist.item :href="route('team.members.edit')" wire:navigate>{{ __('Members') }}</flux:navlist.item>
                @endcan
            </flux:navlist.group>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
