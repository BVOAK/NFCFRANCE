<style>
    @keyframes loader-spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .ui-anim-spinner {
        border: 3.6px solid var(--ui-e-anim-preloader-color);
        box-sizing: border-box;
        overflow: hidden;
        width: 36px;
        height: 36px;
        left: 50%;
        top: 50%;
        animation: loader-spin 2s linear infinite reverse;
        filter: url(#goo);
        box-shadow: 0 0 0 1px var(--ui-e-anim-preloader-color) inset;
    }

    .ui-anim-spinner:before {
        content: "";
        position: absolute;
        -webkit-animation: loader-spin 2s cubic-bezier(0.59, 0.25, 0.4, 0.69) infinite;
        animation: loader-spin 2s cubic-bezier(0.59, 0.25, 0.4, 0.69) infinite;
        background: var(--ui-e-anim-preloader-color);
        transform-origin: top center;
        border-radius: 50%;
        width: 150%;
        height: 150%;
        top: 50%;
        left: -12.5%;
    }
</style>
<div class="ui-anim-spinner">
    <svg>
        <defs>
            <filter id="goo">
                <feGaussianBlur in="SourceGraphic" stdDeviation="2" result="blur" />
                <feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 5 -2" result="gooey" />
                <feComposite in="SourceGraphic" in2="gooey" operator="atop" />
            </filter>
        </defs>
    </svg>
</div>