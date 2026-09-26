<script>
    try {
        if (localStorage.getItem('devfolio-theme') === 'dark') {
            document.documentElement.dataset.theme = 'dark';
        }
    } catch {
        // The default light theme remains available when storage is blocked.
    }
</script>
