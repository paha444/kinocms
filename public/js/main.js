var control = '';
var btnmobile = '';

{
     let checkboxAll = document.querySelectorAll('.data__table .cb');
     let control;
     let checkboxLine;
     if (checkboxAll.length){
          checkboxLine = Array.prototype.slice.call(checkboxAll,1);
          control = checkboxAll[0];
     }
   
     control.addEventListener('change', function () {
          if (!checkboxLine.length) {
               return
          }
          for (let i = 0; i < checkboxLine.length; i++) {
               checkboxLine[i].checked = this.checked;
          }
     });

}