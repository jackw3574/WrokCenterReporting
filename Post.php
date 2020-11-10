<?php 
header("Content-Type: application/json");
$barcode = $_REQUEST['barcode'];
$UserID = $_REQUEST['StationID'];
$Run = strtoupper(substr($barcode,0,strlen($barcode)-3));
$PLIN = substr($barcode,-3,2);
$Cart = substr($barcode,-1);
$DET_SEQ = json_decode(stripslashes($_REQUEST['DET_SEQ']));
$Lines = '';
foreach($DET_SEQ as $d){
	if($Lines == ''){
		$Lines = "'$d'";
	}else{
		$Lines .= ",'$d'";
	}
}

include_once ("C:/apache24/htdocs/connect.php");

$IBM = new IBM();
$SQL = new PDO("odbc:MarshSQL");

	$query = "			
	SELECT 
		[UserName],
		[Password],
		[BOMLevel],
		[DeptNumber]
	FROM 
		[CI_DB].[dbo].[Frontier_Dert]
	Where [UserID] = '$UserID'
	";
$result = $SQL -> query( $query );
$rows = $result -> fetchAll(PDO::FETCH_ASSOC);

foreach($rows as $row){
	$usr = trim($row['UserName']);
	$pswd = trim($row['Password']);
	$M1PColumn =trim($row['BOMLevel']);
	$DeptNumber =trim($row['DeptNumber']);
} 



	$query = "
		WITH CTE(Parent,OMON,VDESC,DET_SEQ,OQ,SQ,SR,BL)
		AS ( Select
				CSP.OMON
				,CSP.OMON
				,CDP.VDESC
				,CDP.DET_SEQ
				,M1P.ML1OQ
				,M1P.ML1SQ
				,M1P.ML1SR
				,0
			FROM 
				FRNDTA040.CT@CHP CHP
				INNER JOIN FRNDTA040.CT@CDP CDP ON
								CHP.RUN = CDP.RUN
								AND CHP.PLIN = CDP.PLIN
								AND CHP.CART = CDP.CART
				INNER JOIN FRNDTA040.CT@CSP CSP ON
								CHP.RUN = CSP.RUN
								AND CHP.PLIN = CSP.PLIN
								AND CHP.CART = CSP.CART
								AND CDP.DET_SEQ = CSP.DET_SEQ
				INNER JOIN FRNDTA040.M1P M1P ON
								CSP.OMON = M1P.M1ON
			where 
				CHP.RUN = '$Run' 
				And CHP.PLIN = '$PLIN'
				AND CHP.CART = '$Cart'
			Group by
				CSP.OMON
				,CSP.OMON
				,CDP.VDESC
				,CDP.DET_SEQ
				,M1P.ML1OQ
				,M1P.ML1SQ
				,M1P.ML1SR
			UNION ALL      
			SELECT 
				   MOFMO
				   ,MOMO
				   ,CTE.VDESC
				   ,CTE.DET_SEQ 
				   ,M1P.ML1OQ
				   ,M1P.ML1SQ
				   ,M1P.ML1SR
				   ,CTE.BL + 1
			FROM 
				   FRNDTA040.MOP MOP 
				   INNER JOIN FRNDTA040.M1P M1P on 
								   MOP.MOMO = M1P.M1ON
				   INNER JOIN CTE CTE ON 
								   CTE.OMON = MOP.MOFMO
		 )
		SELECT 
			CTE.*,
			M2P.ML2DP,
			M2P.ML2WC
		FROM 
			CTE 
			INNER JOIN FRNDTA040.M2P M2P on OMON = M2ON and ML2WC ='1000'
		WHERE 
			SQ < OQ
			and DET_SEQ in ($Lines)
		ORDER BY 
			DET_SEQ

	";
	echo $query;
	$IBM->query($query);
	$i = 1;
	$query = "
			Insert Into MARSHDB.WCR (RUN,PLIN,CART,OMON,ML2DP,ML2WC,Qty,UserID,UserName) 
			Values";
	while ($row = $IBM->fetch()){
		$query .= "
				(
				'$Run',
				'$PLIN',
				$Cart,
				'{$row['OMON']}',
				{$row['ML2DP']},
				'{$row['ML2WC']}',
				{$row['OQ']} - {$row['SQ']},
				$UserID,
				'$usr'),";
		$i++;
	}
	$query = substr($query, 0, -1);
	//echo $query;
	$IBM->query($query);




	$data = array(
		'Run' => "$Lines",
	);
	echo json_encode($data); 

 


?> 