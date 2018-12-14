<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $campaign_title; ?></title>
	<script>
		function redirectUser(){
			window.location = '<?php echo $campaign_url; ?>';
		}
	</script>
<?php echo $header_code; ?>

</head>
<body onload="setTimeout('redirectUser()', 300)">

<?php echo $footer_code; ?>

</body>
</html>