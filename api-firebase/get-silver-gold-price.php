<?php 
header('Content-Type: application/json');
include('../includes/db.php');
$sqlqry = "SELECT * FROM silver_gold_price";
$res = mysqli_query($con,$sqlqry);
$count = mysqli_num_rows($res);

if($count>0){
   while($row=mysqli_fetch_assoc($res)){
      $res_data []=$row;
   }
   echo json_encode(['status'=>'true', 'data'=>$res_data]);
   
}else{
   echo json_encode(['status'=>'false', 'data'=>$res_data,'message'=>'No record found!']);

}

?>