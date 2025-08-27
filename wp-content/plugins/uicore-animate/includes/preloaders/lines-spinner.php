<style>
    .ui-anim-lines {
        width: 80px;
        height: 40px;
    }

    .ui-anim-lines .line {
        width: 80px;
        height: 10px;
        background-color: var(--ui-e-anim-preloader-color);
        position: absolute;
        clip: rect(0, 0, 20px, 0);
    }

    .ui-anim-lines .line.line-1 {
        top: 0;
        -webkit-animation: uiAnimLoader 2s ease 0s infinite;
        animation: uiAnimLoader 2s ease 0s infinite;
    }

    .ui-anim-lines .line.line-2 {
        top: 15px;
        -webkit-animation: uiAnimLoader 2s ease 0.25s infinite;
        animation: uiAnimLoader 2s ease 0.25s infinite;
    }

    .ui-anim-lines .line.line-3 {
        top: 30px;
        -webkit-animation: uiAnimLoader 2s ease 0.5s infinite;
        animation: uiAnimLoader 2s ease 0.5s infinite;
    }

    @keyframes uiAnimLoader {
        0% {
            clip: rect(0, 0, 20px, 0);
        }

        30% {
            clip: rect(0, 80px, 20px, 0);
        }

        50% {
            clip: rect(0, 80px, 20px, 0);
        }

        80% {
            clip: rect(0, 80px, 20px, 80px);
        }

        100% {
            clip: rect(0, 80px, 20px, 80px);
        }
    }
</style>
<div class="ui-anim-lines">
    <div class="line line-1"></div>
    <div class="line line-2"></div>
    <div class="line line-3"></div>
</div>