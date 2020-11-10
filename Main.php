<html>
    <meta charset="utf-8">
	<head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    </head>
	<style>
	*{font-family:Consolas;}
	hr {
	  margin-top: 0.5em;
	  margin-bottom: 0.5em;
	  margin-left: auto;
	  margin-right: auto;
	  border-style: inset;
	  border-width: 5px;
	}
	th, td {
		border: 2.5px solid black;
	}
	td{
		text-align:center;
		font-Size:30px
	}
	.Reorder,.DERT,.btn {
		border:none;
	}
	.DERT{
		width:175px;
	}
	.Reorder{
		background-color: #FF5C39;
	}
	.DESC{
		text-align:left;
		padding: 10px 25px 10px;
	}
	.Small{
		padding:  10px 25px 10px;
		width:75px
	}
	.Cabinet {
		font-Size:65px;
	}
	.Title{
		text-align:center;
		font-Size:90px
	}
	.barcode{
		font-Size:40px;
		display:inline-block
	}
	.select{
		font-Size:30px;
		border:none;
	}
	.option{
		font-Size:20px;
	}
	.button {
		text-align:center;
		font-Size:30px;
		border: none; 
		color:black;
		text-decoration: none;
		display: inline-block;
		padding: 0px 15px;
		margin: 10px 10px;
		border-radius: 25px;
	}
	.button:focus {     
		background-color:#FF8166;    
	}
	.green{
		background-color: Green;
	}
	.yellow{
		background-color: yellow;
	}
	.StrikeThrough {
	  text-decoration: line-through;
	}

	</style>

	<div id="Main">
		<div class='barcode' >Run Number:</div><input class='barcode' style="text-transform: uppercase;" id='barcodeID' type='text'></input>
		<form>
			<table id="OutPutTable" style='Width:100%'></table>
		</form>
		<button onclick='CountComplete()'>Submit</button>

		
	</div> 
	<div id="Loading" style='Display:none'><h1>Loading...<h1></div>

	<script>
		var barcode = '';
		StationID = '<?php echo $_REQUEST['StationID'];?>';
		var  RUN = [], CurrentPart = '';
		barcodeID = $('#barcodeID');
		
		document.onkeypress = function(e){
            if (e.keyCode == 13){
				$("#OutPutTable tr").remove(); 
                console.log(barcodeID.val());
				$.ajax({
					url:'Get.php',
					data:{
						'barcode':barcodeID.val(),
						'StationID':StationID,
					},
					beforeSend: function(jqXHR, settings) {
						console.log(settings.url);
					},						
					success: function(response){
						console.log(response)
						RUN = [];
						RUN = response
						UpdateScreen();
					},
					error: function(xhr,status,error){
						console.log(error);
						barcodeID.val("Please Try Again");
					}
				});
				CurrentBarcode = barcodeID.val();
				barcode = '';
            } else {
				if(barcodeID.is(':focus')){
					barcode = barcodeID.val().toUpperCase();
				}else{
					barcode = barcode + String.fromCharCode(e.keyCode).toUpperCase();
					barcodeID.val(barcode);
				}
            }        		
		}
		function UpdateScreen() {	
			var table = document.getElementById("OutPutTable");
			var header = table.insertRow(-1);
				var h1 = header.insertCell(-1);
					h1.innerHTML = 'Complete?'
					//h1.className = 'Complete Btn'
				var h2 = header.insertCell(-1);
					h2.innerHTML = 'Description'
					h2.className = 'Small'
				var h3 = header.insertCell(-1);
					h3.innerHTML = 'Completed/Ordered'
					h3.colSpan = 2;
					//h3.className = 'Small'

			for (i in RUN){
				if (RUN[i].DET_SEQ != CurrentPart){
					var CurrentPart = RUN[i].DET_SEQ;
					var row = table.insertRow(-1);
						row.id = CurrentPart;
						var cell_1 = row.insertCell(-1);
							cell_1.innerHTML = "<input checked type='checkbox' id='"+CurrentPart+"box' name='"+CurrentPart+"' value= '"+CurrentPart+"'>";
							cell_1.className = 'Complete btn';
						var cell_2 = row.insertCell(-1);
							cell_2.innerHTML = RUN[i].VDESC;
							//cell_2.style.textAlign = 'left';
							//cell_2.className = 'Small'
						var cell_3 = row.insertCell(-1);
							cell_3.val = 0;
							//cell_3.className = 'Small'
							cell_3.id = CurrentPart + 'SR'
						var cell_4 = row.insertCell(-1);
							cell_4.val = 0;
							//cell_4.className = 'Small'
							cell_4.id = CurrentPart + 'OQ'
				}
				var OQ = document.getElementById(RUN[i].DET_SEQ + 'OQ');
					OQ.val = OQ.val + RUN[i].OQ;
					OQ.innerHTML = OQ.val
				var SR = document.getElementById(RUN[i].DET_SEQ + 'SR');
					SR.val = SR.val + RUN[i].SR;
					SR.innerHTML = SR.val
				var Checkbox = document.getElementById(RUN[i].DET_SEQ + 'box');
				if (OQ.val == SR.val){
					Checkbox.checked = false;
				}else{
					Checkbox.checked= true;
				}
			}
			

			
		}
		function CountComplete() {
			 var DET_SEQ = document.forms[0];
			var CompeteDET_SEQ = [];
			var i;
			for (i = 0; i < DET_SEQ.length; i++) {
				if (DET_SEQ[i].checked) {
					CompeteDET_SEQ.push(DET_SEQ[i].value) ;
				}
			}
			Complete(CompeteDET_SEQ); 
		}
		function Complete(DET_SEQ) {
			var jsonString = JSON.stringify(DET_SEQ);
			$.ajax({
				url:'Post.php',
				data:{
						'StationID':StationID,
						'barcode':barcodeID.val(),
						'DET_SEQ':jsonString,
					},
				beforeSend: function(jqXHR, settings) {
						console.log(settings.url);
					},
				success: function(response){
					for (i = 0; i < DET_SEQ.length; i++) {
						var SR = document.getElementById(DET_SEQ + 'SR');
							SR.val = 99;
							SR.innerHTML = SR.val
					}
				},
				error: function(xhr,status,error){
					console.log('Nope');
					//barcodeID.html("Something Went Wrong, Please Try Again");
				}
			}); 
		}
		
$( document ).ajaxStart(function() {
	 $( "#Main" ).hide();
    $( "#Loading" ).show();
});
$( document ).ajaxStop(function() {
	$( "#Main" ).show();
    $( "#Loading" ).hide();
});
	</script>
	


</html>