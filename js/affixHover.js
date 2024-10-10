<script>
        function updateDateTime() {
            now = new Date();
            const dateTimeString = now.toLocaleString();
            document.getElementById('dateTime').textContent = dateTimeString;
        
        updateDateTime();
        setInterval(updateDateTime, 1000);
</script>