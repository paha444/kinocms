//alert(map_coordinates);

//map_coordinates = map_coordinates.split(',');

//alert(map_coordinates);

function init() {
    
    //alert(map_coordinates);
    
    var myMap = new ymaps.Map('map', {

            //center: [55.72103556903898,37.60208549999989],
            center: [map_coordinates_x,map_coordinates_y],
            
            zoom: 16,
            
            controls: ['zoomControl'],
            //controls: ['zoomControl', 'searchControl', 'typeSelector',  'fullscreenControl', 'routeButtonControl'],


            type: 'yandex#map',

            //behaviors: ['scrollZoom', 'drag']
            

        }); //{
           // searchControlProvider: 'yandex#search'
        //}),

 
       

		myPlacemark = new ymaps.Placemark(

            myMap.getCenter(),
            //zoom: 13,
/*
            //[44.976656, 34.138532],
            { hintContent: 'Имиджстудия',
            //zoom: 15,
            balloonContent: 'Россия, Москва, Ленинский проспект 13, 5 минуты от м. Шаболовская' },
            { draggable: false,
            iconImageHref: '/images/map_marker.png',
            //iconImageSize: [235, 269],
            //iconImageOffset: [-125, -283]}
            }*/
        );



        // Слушаем клик на карте.
        myMap.events.add('click', function (e) {
            var coords = e.get('coords');
    
           // Если метка уже создана – просто передвигаем ее.
           // if (myPlacemark) {
                myPlacemark.geometry.setCoordinates(coords);
           // }
            // Если нет – создаем.
          //  else {
/*                myPlacemark = createPlacemark(coords);
                myMap.geoObjects.add(myPlacemark);
                // Слушаем событие окончания перетаскивания на метке.
                myPlacemark.events.add('dragend', function () {
                    getAddress(myPlacemark.geometry.getCoordinates());
                });*/
           // }

            //alert(coords);
            getAddress(coords);
        }); 


    // Определяем адрес по координатам (обратное геокодирование).
    function getAddress(coords) {
        myPlacemark.properties.set('iconCaption', 'поиск...');

/*geoCoder = ymaps.geocode(coords);
geoCoder.then(function(result) {
     console.log(result.geoObjects.getLocalities());
});
*/


        ymaps.geocode(coords).then(function (res) {
            var firstGeoObject = res.geoObjects.get(0);
            
            myPlacemark.properties
                .set({
                    // Формируем строку с данными об объекте.
                    iconCaption: [
                        // Название населенного пункта или вышестоящее административно-территориальное образование.
                        firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
                        // Получаем путь до топонима, если метод вернул null, запрашиваем наименование здания.
                        firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                    ].filter(Boolean).join(', '),
                    // В качестве контента балуна задаем строку с адресом объекта.
                    balloonContent: firstGeoObject.getAddressLine()
                    
                    
                    
                    
                    
                });
                
            var address  = firstGeoObject.getAddressLine()    
            
          //  $('#map_address').html('<i class="icon-onmap"></i>'+firstGeoObject.getAddressLine());    
            //$('#map_coordinates').val(coords);    
              
          //  document.getElementById("map_coordinates").value = coords;
                
        });
   
    
    }
        

        //calculator = new DeliveryCalculator(myMap, myMap.getCenter());
    
    
    //myMap.behaviors.disable('scrollZoom');
    //myMap.behaviors.disable('SearchControl');
 

    //myMap.controls.add(searchStartPoint, { left: 5, top: 5 });

    //myMap.controls.add(searchFinishPoint, { left: 5, top: 5 });

	myMap.controls.add('zoomControl', { top: 110, right: 5 });
    myMap.controls.add('searchControl');

	myMap.geoObjects.add(myPlacemark);
    
    
    //getAddress([map_coordinates_x,map_coordinates_y]);
    
    //myMap.setCenter('55.74771794154334','37.58876210297389');

 
}


ymaps.ready(init);


