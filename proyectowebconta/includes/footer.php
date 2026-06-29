    </main>
</div>
<script src="assets/js/app.js"></script>
<script>
async function logout() {
    await fetch('api/auth.php?action=logout');
    window.location.href = 'login.php';
}
</script>
</body>
</html>
