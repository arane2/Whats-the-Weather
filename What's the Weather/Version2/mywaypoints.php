<!DOCTYPE html>
<html>
<head>
    <title>
        MyWayPoints
    </title>
    <h3>MyWayPoints</h3>
</head>
<body>

    
    <style>
        #rightalign {
        margin-right: 200px; 
        }
        #alignsubmitbutton {
        margin-left: 300px
        }
    </style>

    <p>
    <form action="code1.php" method="POST">
    Current City:       <input id="rightalign" type="text" name="start" value="Buffalo">
    Destination City:   <input  type="text" name="end" value="Miami"><br>
                        <input id="alignsubmitbutton" type="submit" value="Submit">
    </form>
    </p>

    <div id="googleMap" style="width:100%;height:480px;"></div>
    <script>
    function myMap() {
    var mapProp= {
    center:new google.maps.LatLng(39.8283, -98.5795),
    zoom:4,
    };
    var map=new google.maps.Map(document.getElementById("googleMap"),mapProp);

    }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAZzV7daW1ZQ7ho8rVzeMbIbIxrbD5hz64&callback=myMap"></script>

</body>
</html>