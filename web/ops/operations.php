<?php 

    // First we execute our common code to connection to the database and start the session 
    require("common.php"); 
     
    // At the top of the page we check to see whether the user is logged in or not 
    if(empty($_SESSION['user'])) 
    { 
        // If they are not, we redirect them to the login page. 
        header("Location: login.php"); 
         
        // Remember that this die statement is absolutely critical.  Without it, 
        // people can view your members-only content without logging in. 
        die("Redirecting to login.php"); 
    } 
     
    // Everything below this point in the file is secured by the login system 
    // We can display the user's username to them by reading it from the session 
    // array.  Remember that because a username is user submitted content we must 
    //use htmlentities on it before displaying it to the user. 
?> 
<html>
<head>
<title>Fox Operations</title>
<?php include "../head.php"; ?>
<?php include "../getName.php"; ?>

<link rel="stylesheet" type="text/css" media="all" href="http://www.amsat.org/wordpress/wp-content/themes/twentyeleven-amsat-child/style.css" />
</head>
<body>
<?php include "header.php"; ?>
<?php include "../id_loopup.php"; ?>
<h1 class="entry-title">AMSAT OPERATIONS </h1>
Logged in as: <?php echo htmlentities($_SESSION['user']['username'], ENT_QUOTES, 'UTF-8'); ?><br /> 
<?php if(($_SESSION['user']['admin']) >= '5') { 
echo '
<h3>Maintenance</h3>
<table>
<tr><td>FOX-1B</td>
<td>
<a href="show_t0id.php?id=2">Calculate Time Zero for a Reset</a><br /> 
<a href="edit_t0id.php?id=2">Edit Time Zero Resets File</a><br /> 
</td></tr>
<tr><td>MESAT-1</td>
<td>
<a href="show_t0id.php?id=10&series=LTM">Calculate Time Zero for a Reset</a><br /> 
<a href="edit_t0id.php?id=10&series=LTM">Edit Time Zero Resets File</a><br /> 
</td></tr>
</table>
<h3>Older Spacecraft</h3>
<table>
<tr><td>FOX-1A</td>
<td>
<a href="show_t0id.php?id=1">Calculate Time Zero for a Reset</a><br /> 
<a href="edit_t0id.php?id=1">Edit Time Zero Resets File</a><br /> 
</td></tr>
<tr><td>FOX-1C</td>
<td>
<a href="show_t0id.php?id=3">Calculate Time Zero for a Reset</a><br /> 
<a href="edit_t0id.php?id=3">Edit Time Zero Resets File</a><br /> 
</td></tr>
<tr><td>FOX-1D</td>
<td>
<a href="show_t0id.php?id=4">Calculate Time Zero for a Reset</a><br /> 
<a href="edit_t0id.php?id=4">Edit Time Zero Resets File</a><br /> 
</td></tr>
<tr><td>FOX-1E</td>
<td>
<a href="show_t0id.php?id=5">Calculate Time Zero for a Reset</a><br /> 
<a href="edit_t0id.php?id=5">Edit Time Zero Resets File</a><br /> 
</td></tr>
<tr><td>HuskySat</td>
<td>
<a href="show_t0id.php?id=6">Calculate Time Zero for a Reset</a><br /> 
<a href="edit_t0id.php?id=6">Edit Time Zero Resets File</a><br /> 
</td></tr>
</table>
';
}
?>
<h3>Admin</h3>
<a href="memberlist.php">Ops Team</a><br /> 
<?php
if(($_SESSION['user']['admin']) == '10') { 
   echo '<a href="register.php">Add User</a><br />'; 
   echo '<a href="delete_user.php">Delete User</a><br />'; 
}
?>
<a href="edit_account.php">Edit This Account</a><br /> 
<a href="logout.php">Logout</a>
