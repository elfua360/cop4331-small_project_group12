<?php
session_start();

if(!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'])
{
    header('Location: index.php');
}

?>
<!DOCTYPE html>
<html>
<head>
<script src="jquery-3.5.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="CSS/ContactZ.css">
<meta charset="ISO-8859-1">
<style>
body {
  font-family: Arial, Helvetica, sans-serif;
}
    
* {
  box-sizing: border-box;
}

.row {
    display: flex;
}

.left {
    flex: 35%;
    padding: 15px 0;
}

.left h2 {
  padding-left: 8px;
}

/* Right column (page content) */
.right {
  flex: 65%;
  padding: 0px;
  text-align:center;
  font-family: verdana;
  font-size:20px;
}

#edit {
    list-style-type: none;
    margin-top: 0;
    margin-right: 0;
    margin-left: 0;
    padding: 0;
    overflow: hidden;
    background-color: #e6ffe9;   
}
    
#edit li:hover {
   background-color: #eee;      
}
    
#edit li {
    float: right;
    display: block;
    color: black;
    padding: 14px 16px;
}
    
#done {
    list-style-type: none;
    margin-top: 0;
    margin-right: 0;
    margin-left: 0;
    padding: 0;
    overflow: hidden;
    background-color: #e6ffe9;   
}
    
#done li:hover {
   background-color: #eee;      
}
    
#done li {
    float: right;
    display: block;
    color: black;
    padding: 14px 16px;
}

    
    
/* Style the search box */
#search {
  width: 100%;
  font-size: 18px;
  padding: 11px;
  border: 1px solid #ddd;
}

/* Style the navigation menu inside the left column */
#menu {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

#menu li {
  padding: 12px;
  text-decoration: none;
  color: black;
  display: block
}

#menu li:hover {
  background-color: #eee;
}    
</style>
<title>ContactZ Online Contact Manager</title>
<?php echo "<h1>" . "Welcome, " . $_SESSION['name'] . "</h1>"?>
<p align="right">
<button onclick="addContact();">Add Contact</button>
<button onclick="logout();">Log Out</button>
</p>


</head>
<!-- ----------------------------HEAD ENDS HERE--------------------------------------- -->


<!-- ---------------------------BODY STARTS HERE-------------------------------------- -->
<body>

	<div class="row">
		<div class="left" style="background-color: #fffa99;">
			<h2>List of Contacts</h2>
			<input type='text' id='search' placeholder='Search for a contact...' title='Contact search'>
            <ul id = "menu">
            </ul>
		</div>
        
		<div class="right" style="background-color: #fce6ff;">
            <!--<ul id ="edit"> 
            </ul>  -->
			<h2 id="title">Click on a name to see information</h2>
		</div>
	</div>


<script>
$(document).ready(function() {
    $.get("API/searchContact.php", {contact : JSON.stringify({"contact" : ""})},
         function(data, status) {
            $("#menu").empty();
            console.log(data);
            console.log("test");
            // check for no results
            var contacts = JSON.parse(data);
            length = contacts.length;
            for (var i =0 ; i < length; i++) {
                var out = "<li id='" + contacts[i]['id'] + "'>" + contacts[i]['firstname'] + " " + contacts[i]['lastname'] + "</li>";
                $("#menu").append(out);   
            }
    });
    $("#search").on('input',function() {
        $("#menu").empty();
        $.get("API/searchContact.php", {contact : JSON.stringify({"contact" : $("#search").val()})},
         function(data, status) {
            console.log(data);
            if(data == "-1");
 //               $("ul").append("<p style='color:red'>No Results Found</p>");
            else {
                var contacts = JSON.parse(data);
                length = contacts.length;
                for (var i =0 ; i < length; i++) {
                    var out = "<li id='" + contacts[i]['id'] + "'>" + contacts[i]['firstname'] + " " + contacts[i]['lastname'] + "</li>";
                    if($("#" + contacts[i]['id']).length <= 0)
                        $("#menu").append(out);   
                }
            }    
        });
    });
    $("#menu").on('click','li',function(event) {
        $(".right").empty();
        $.get("API/retrieveContact.php", {contact : JSON.stringify({"id" : event.target.id})},
             function(data, status) {
                console.log(data);
                var contacts = JSON.parse(data);
                $(".right").prepend("<ul id= 'edit'></ul>");
                $("#edit").append("<li id='e" + event.target.id + "'>Edit Contact</li>");
                $("#edit").append("<li id='d" + event.target.id + "'>Delete Contact</li>");
                $(".right").append("<h2>" + contacts['firstname'] + " " + contacts['lastname'] + "</h2>");
                $(".right").append("<p>Phone Number: " + contacts['number']);
                $(".right").append("<p>Email: TEMP</p>" );
            
        });
    });
    $(document).on('click', '#edit', function(event) {
        var type = event.target.id.charAt(0);
        var contact = event.target.id.substring(1);
        console.log(contact);
        console.log("click!");
        if (type == "d")
        {
            console.log("d found");
            $.get("API/deleteContact.php", {delete : JSON.stringify({"id" : parseInt(contact)})},
                 function(data, status){
                    if (data == "1")
                        location.reload();
                    console.log(data);
            });
        }
        
        else if (type == "e")
        {
           $.get("API/retrieveContact.php", {contact : JSON.stringify({"id" : contact})},
                 function(data, status) {
                    var info = JSON.parse(data);
                    editContact(contact, info["firstname"], info["lastname"], info["number"]);
                });
        }
    });
});

$(document).on('click', '#done', function(event) {
    var id = event.target.id.substring(1);
    var payload = {contact : JSON.stringify({"id" : parseInt(id), "firstname" : $("#fname").val(), "lastname" : $("#lname").val(), "number" : $("num").val()})};
    $.post("API/updateContact.php", payload, function(result) {
        console.log(result);
        location.reload();
    
    });
});
function editContact(id,fname,lname,num) {
    $(".right").empty();
    $(".right").prepend("<ul id= 'done'></ul>");
    $("#done").append("<li id='u" + id + "'>Done</li>");
    $(".right").append("<form id='f'></form>");
    $("#f").append("<label for='fname'>First name:</label>");
    $("#f").append("<input type='text' id='fname' name='fname' value='" + fname + "'><br><br>")
    $("#f").append("<label for='lname'>Last name:</label>");
    $("#f").append("<input type='text' id='lname' name='lname' value='" + lname + "'><br><br>")
    $("#f").append(" <label for='num'>Phone Number:</label>");
    $("#f").append("<input type='text' id='num' name='num' value='" + num + "'><br><br>")
}
function logout() {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() 
    {
        if (this.readyState == 4 && this.status == 200) 
        {
            console.log(this.responseText);
            window.location.replace('index.php');
        }
    };
    xhttp.open("GET", "API/logout.php", true);
    xhttp.send();
}

function addContact() {
    window.location = 'addcontact.php';
}
   
/*function getInfo1() { 
    
	document.getElementById("title").innerHTML = "Contact Information";
  	document.getElementById("firstname").innerHTML = "First Name: Andy";
  	document.getElementById("lastname").innerHTML = "Last Name: Baker";
  	document.getElementById("phone").innerHTML = "Phone Number: 407-123-4567";
  	document.getElementById("email").innerHTML = "Email: andy.baker@ucf.edu";
}
function getInfo2() { 
	document.getElementById("title").innerHTML = "Contact Information";
	  document.getElementById("firstname").innerHTML = "First Name: Jane";
	  document.getElementById("lastname").innerHTML = "Last Name: Doe";
	  document.getElementById("phone").innerHTML = "Phone Number: 407-987-6543";
	  document.getElementById("email").innerHTML = "Email: jane.doe@ucf.edu";
	}
function getInfo3() { 
	document.getElementById("title").innerHTML = "Contact Information";
	  document.getElementById("firstname").innerHTML = "First Name: John";
	  document.getElementById("lastname").innerHTML = "Last Name: Smith";
	  document.getElementById("phone").innerHTML = "Phone Number: 407-555-5555";
	  document.getElementById("email").innerHTML = "Email: john.smith@ucf.edu";
	}
function delete1(){
	  if (confirm("Are you sure you want to delete this contact?")) {
		  <!-- needs function to actually remove contact from database.-->
		  alert("Contact has been deleted.");
		  document.getElementById("title").innerHTML = "Click on a name to see information";
		  document.getElementById("firstname").innerHTML = "First Name:";
		  document.getElementById("lastname").innerHTML = "Last Name:";
		  document.getElementById("phone").innerHTML = "Phone Number:";
		  document.getElementById("email").innerHTML = "Email:";
		  
	  } else {
	    alert("Contact has not been deleted.")
	  }
}*/
</script>

</body>
<!-- -----------------------------BODY ENDS HERE-------------------------------------- -->
</html>