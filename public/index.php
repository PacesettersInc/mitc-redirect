<html>
<head>
    <title>MITC</title>
</head>
<body>
<h3>
Please wait...

<div id="links" style="display:none">
    <hr>
    Please choose from a link below.<br>
    <a href="http://mitc1.pacesetterstn.com/MyMITC">MyMITC Link #1</a><br>
    <a href="http://mitc2.pacesetterstn.com/MyMITC">MyMITC Link #2</a>
</div>
</h3>
<script src="/js/jquery.min.js"></script>
<script>
$(function() {
    var urls = [
    "http://mitc1.pacesetterstn.com/MyMITC/",
    "http://mitc2.pacesetterstn.com/MyMITC/"
    ];

    function redirect(url) { 
        window.location.href = url;
    }

    function testHost(url) {
        var mitc = new Image();

        mitc.onload = function() {
            redirect(url);
       };

        mitc.onerror = function(e) {
        };

        mitc.src = url + "Images/mymitc_10.gif?_t=" + (new Date().getTime());
    }

    setTimeout(function() {
        $("#links").fadeIn();
    },3000);

    setTimeout(function() {
        testHost(urls[0])
    },1);

    setTimeout(function() {
        testHost(urls[1]);
    },1);
});
</script>
</body>
</html>