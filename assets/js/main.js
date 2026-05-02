<script>
document.querySelectorAll('.tab-btn').forEach(btn = {
    btn.addEventListener('click', () => {
        // toggle tombol
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // toggle panel
        const target = btn.getAttribute('data-target');
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        document.getElementById(target).classList.add('active');
    })
})
</script>
