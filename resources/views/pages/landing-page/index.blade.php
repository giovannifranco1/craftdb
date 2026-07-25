<x-layouts.landing-page :title="__('landing-page.meta.index_title')">
    @include('pages::landing-page.sections.hero')
    @include('pages::landing-page.sections.logo-strip')
    @include('pages::landing-page.sections.features')
    @include('pages::landing-page.sections.how-it-works')
    @include('pages::landing-page.sections.showcase')
    @include('pages::landing-page.sections.pricing')
    @include('pages::landing-page.sections.faq')
</x-layouts.landing-page>
