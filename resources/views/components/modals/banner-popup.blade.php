@php
$bannerPopup = \App\Models\Banner::where('location', \App\Enums\BannerLocation::Popup)
->where('status', 'published')
->get()
->first();

// Check if user has seen the banner in the last hour
$lastSeen = session('banner_popup_last_seen');
$showBanner = !$lastSeen || (time() - $lastSeen) > 3600;

if ($showBanner) {
// Store current timestamp in session
session(['banner_popup_last_seen' => time()]);
}
@endphp

@if($bannerPopup && $showBanner)
<div>
    <x-modal ref="banner-popup" size="popup" closeColor="white" open="true" theme="transparent" delay="3">
        <x-common.banner gap="1" location="Popup" />
    </x-modal>
</div>
@endif