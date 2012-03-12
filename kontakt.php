<?php 
	include "codeheader.php"; 
	include $incdir . "htmlheader.php"; 
	
?>



<body id="page_bg">

<div id="container">

<?php
include "header.php";
echo <<<FLA
		<a href="index.php">
			<div id="homelink"></div>
		</a>
FLA;
?>		
		

    </div>

	
    
    <div id="maincontent">
			<div id="journalentry"> 

				<div id="journalentrytitel"> 
				Hallo! Sie wollen mich kontaktieren?
				</div> 
				
				<div id="journalentrycontent"> 
					<span class="journalentrycontent"> 
                        <div id="journalpic"> 
								<img src="images/about.jpg" border="0" alt="yeah yeah self portrait" /> 
						</div> 

						<h1 class="normal_content">Am besten per email</h1> 


						<p>
							<a href="javascript: norobotmail('office', 'andreaszeitler.net');">hier klicken!</a>
						</p>

						                          
						 <br /> <br />
						<h1 class="normal_content">Bis bald</h1>
						<p>
							Ich freue mich schon auf Ihre Nachricht...
						</p>
					</span> 
				</div> 
 
			</div> 
    </div>

	<div id="footer">
		<?php include "footer.php" ?>
    </div>


</div>

</body>
</html>

