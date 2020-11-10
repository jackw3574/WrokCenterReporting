<?php
header("Content-Type: application/json");

$barcode = $_REQUEST['barcode'];

$Run = strtoupper(substr($barcode,0,strlen($barcode)-3));
$PLIN = substr($barcode,-3,2);
$Cart = substr($barcode,-1);



	$usr = "SCANOUT";
	$pswd = "buzzed";
	$conn = odbc_connect("Driver={IBM i Access ODBC Driver};System=172.29.200.10;Database=MARSHPRD;,*USRLIBL", $usr, $pswd);

 if ($conn){
	$query = "
WITH CTE(Parent,OMON,VDESC,DET_SEQ,OQ,SQ,SR,BL)
AS ( Select
		CSP.OMON
		,CSP.OMON
		,CDP.VDESC
		,CDP.DET_SEQ
		,M1P.ML1OQ
		,M1P.ML1SQ
		,COALESCE(WCR.Qty,0)
		--,M1P.ML1SR
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
		Left JOIN MARSHDB.WCR WCR on
						CSP.OMON = WCR.OMON
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
		,WCR.Qty
 )
SELECT
	*
FROM
	CTE
WHERE
	SQ < OQ
ORDER BY
	DET_SEQ
	";
	//echo $query;
	$result = odbc_exec ( $conn, $query );
	$data = array();
	//while (odbc_fetch_array($result)){

	set_time_limit(300);
	 while (odbc_fetch_row($result)){
		array_push($data, array(
			'Parent' => trim(odbc_result($result,'Parent')),
			'OMON' => trim(odbc_result($result,'OMON')),
			'VDESC' => trim(odbc_result($result,'VDESC')),
			'DET_SEQ' => (int)trim(odbc_result($result,'DET_SEQ')),
			'OQ' => (int)trim(odbc_result($result,'OQ')),
			'SQ' => (int)trim(odbc_result($result,'SQ')),
			'SR' => (int)trim(odbc_result($result,'SR')),
			'BL' => (int)trim(odbc_result($result,'BL'))
		));
	}
	echo json_encode($data);

    odbc_close( $conn );


 }else{
	die( "Error connecting to IBM server" );
 }






?>
