 function mobile () {
      let menu = document.querySelector('.menu--open');

      let k = 0;
      menu.addEventListener('click', function () {
           k++;
           if (k === 1) {
                anime({
                     targets: '.menu-wrap',
                     left: ['-105%','0'],
                     duration:400,
                     easing:'easeInQuad',
                });
           } else if (k === 2) {
                anime({
                     targets: '.menu-wrap',
                     left: ['0','-105%'],
                     duration:250,
                     easing:'easeInQuad',
                });
                k=0;
           }
      });
 }
mobile();

 {
      {
           const header = document.querySelector('.menu-wrap .navigations');
           const btns = header.getElementsByClassName("menu__item");
           for (let i = 0; i < btns.length; i++) {
                btns[i].addEventListener("click", function() {
                     let current = document.getElementsByClassName("menu__item--actives");
                     if (current.length > 0) {
                          current[0].className = current[0].className.replace("menu__item--actives", "");
                     }
                     this.className += " menu__item--actives";
                });
           }
      }
 }

 {
      document.querySelector('.menu-icon-wrapper').onclick = function(){
           document.querySelector('.menu-icon').classList.toggle('menu-icon-active');
      };
 }
