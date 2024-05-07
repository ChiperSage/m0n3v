<?php
function makeInt($angka){
  if ($angka < -0.0000001){
    return ceil($angka-0.0000001);
  }else { 
    return floor($angka+0.0000001); 
  }
}
 
function tanggal_hijriah($tanggal){
  $array_bulan = array("Muharram", "Safar", "Rabiul Awwal", "Rabiul Akhir",
             "Jumadil Awwal","Jumadil Akhir", "Rajab", "Sya'ban", 
             "Ramadhan","Syawwal", "Zulqaidah", "Zulhijjah");
 
$date = makeInt(substr($tanggal,8,2));
$month = makeInt(substr($tanggal,5,2));
$year = makeInt(substr($tanggal,0,4));
 
if (($year>1582)||(($year == "1582") && ($month > 10))||(($year == "1582") && ($month=="10")&&($date >14))){
  $jd = makeInt((1461*($year+4800+makeInt(($month-14)/12)))/4)+
  makeInt((367*($month-2-12*(makeInt(($month-14)/12))))/12)-
  makeInt( (3*(makeInt(($year+4900+makeInt(($month-14)/12))/100))) /4)+
  $date-32075; 
} else{
  $jd = 367*$year-makeInt((7*($year+5001+makeInt(($month-9)/7)))/4)+
  makeInt((275*$month)/9)+$date+1729777;
}
 
$wd = $jd%7;
$l = $jd-1948440+10632;
$n=makeInt(($l-1)/10631);
$l=$l-10631*$n+354;
$z=(makeInt((10985-$l)/5316))*(makeInt((50*$l)/17719))+(makeInt($l/5670))*(makeInt((43*$l)/15238));
$l=$l-(makeInt((30-$z)/15))*(makeInt((17719*$z)/50))-(makeInt($z/16))*(makeInt((15238*$z)/43))+29;
$m=makeInt((24*$l)/709);
$d=$l-makeInt((709*$m)/24);
$y=30*$n+$z-30;
$g = $m-1;
$final = "$d $array_bulan[$g] $y H";
return $final;
}
?>