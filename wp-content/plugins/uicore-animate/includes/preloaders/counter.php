<div class="ui-anim-preloader">
    <div id="ui-anim-loader-text">0%</div>
</div>
<style>
    #ui-anim-loader-text {
        position: fixed;
        z-index: 99999999999999999999;
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }

    .ui-anim-preloader {
        transition: opacity .4s ease;
    }
</style>
<script>
    setProgressCookie = (value) => {
        const expires = "; expires=" + new Date(new Date().getTime() + 3600 * 1000).toUTCString();
        document.cookie = "uicore_animate_progress=" + (value || "") + expires + ";";
    }
    getProgressCookie = () => {
        return parseInt(document.cookie.replace(/(?:(?:^|.*;\s*)uicore_animate_progress\s*\=\s*([^;]*).*$)|^.*$/, "$1")) || 0;
    }
    uiAnimateCustomPreloaderHide = () => {

        setTimeout(() => {
            uiAnimatePreloaderHide();
        }, 100);
        setTimeout(() => {
            uiAnimateTogglePreloader(false);
        }, 350);
        setTimeout(() => {
            uiAnimateLoaderText.textContent = Math.round(0) + "%";
            setProgressCookie(0);
        }, 700);

    }

    const uiAnimateLoaderText = document.getElementById("ui-anim-loader-text");
    let initialProgress = getProgressCookie()

    let loadingElements = document.querySelectorAll('img, script, link[rel="stylesheet"]');
    let totalCount = loadingElements.length;
    let loadedCount = 0;
    let lastUpdate = 0;
    const throttleTime = 150;

    function updateLoader() {
        loadedCount++;
        let progress = ((loadedCount / totalCount) * (100 - initialProgress)) + initialProgress;
        progress = progress > 100 ? 100 : progress; // Ensure progress does not exceed 100
        let currentTime = Date.now();
        if (progress >= 100) {
            uiAnimateCustomPreloaderHide();
            return;
        }
        if (currentTime - lastUpdate >= throttleTime || progress === 100) {
            uiAnimateLoaderText.textContent = Math.round(progress) + "%";
            lastUpdate = currentTime;
        }
    }

    loadingElements.forEach(function(element) {
        element.addEventListener('load', updateLoader, false);
        element.addEventListener('error', updateLoader, false);
        if (element.tagName === 'LINK') {
            updateLoader();
        }
    });

    document.onreadystatechange = function() {
        if (document.readyState === "complete") {
            let progress = getProgressCookie();
            let interval = setInterval(() => {
                progress++;
                uiAnimateLoaderText.textContent = Math.round(progress) + "%";
                setProgressCookie(progress)
                if (progress >= 100) {
                    clearInterval(interval);
                    uiAnimateCustomPreloaderHide();

                }
            }, 15);
        }
    }

    window.addEventListener('beforeunload', () => {
        let max = Math.floor(Math.random() * 46) + 36;
        let progress = 0;
        let increaseProgress = () => {
            let increment = Math.floor(Math.random() * 6 + 1);
            progress += increment;

            uiAnimateLoaderText.textContent = Math.round(progress) + "%";
            setProgressCookie(progress)

            if (Math.random() < 0.35) {
                let delay = Math.floor(Math.random() * 90 + 50)
                console.log('delaying with - ' + delay, progress)
                clearInterval(interval);
                setTimeout(() => {
                    interval = setInterval(increaseProgress, 30);
                }, delay);
            }

            if (progress >= max) {
                clearInterval(interval);
            }
        }
        let interval = setInterval(increaseProgress, 40);
    });
</script>