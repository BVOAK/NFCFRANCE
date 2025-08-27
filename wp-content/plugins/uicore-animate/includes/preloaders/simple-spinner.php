<style>
    .ui-anim-spinner {
        animation: uiAnimLoader 1s infinite linear;
        border-radius: 50%;
        border: 3px solid rgba(0, 0, 0, 0.2);
        border-top-color: var(--ui-e-anim-preloader-color);
        height: 0;
        margin: 0 auto 3.5em auto;
        width: 80px;
        height: 80px;
        margin-bottom: 10px;
    }

    @keyframes uiAnimLoader {
        to {
            transform: rotateZ(360deg);
        }
    }
</style>
<div class="ui-anim-spinner"></div>