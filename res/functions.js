function checkDelete(uid, param, label) {
    
    Check = confirm(label);
    if (Check != false)
      
      window.location.href= 'index.php?action=deleteBill&uid='+uid+param;
    
}
