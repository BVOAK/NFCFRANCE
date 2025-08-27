<style>
    .ui-anim-triangle {
        stroke-dasharray: 17;
        animation: uiAnimLoader 2.5s cubic-bezier(0.35, 0.04, 0.63, 0.95) infinite;
    }

    @keyframes uiAnimLoader {
        to {
            stroke-dashoffset: 136;
        }
    }
</style>

<svg width="80" height="80" viewBox="0 0 33 34" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M16 2L32 33H1L16 2Z" stroke="var(--ui-e-anim-preloader-color)" stroke-width="1" class="ui-anim-triangle" />
</svg>