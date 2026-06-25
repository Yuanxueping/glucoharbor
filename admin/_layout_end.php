  </div><!-- /.p-4 -->
</div><!-- /.admin-main -->
</div><!-- /.admin-wrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
var st=document.getElementById('sidebarToggle');
var sb=document.getElementById('sidebar');
if(st)st.addEventListener('click',function(){sb.classList.toggle('open')});
setTimeout(function(){document.querySelectorAll('.alert.fade.show').forEach(function(el){try{new bootstrap.Alert(el).close()}catch(e){}})},4000);
</script>
</body>
</html>
