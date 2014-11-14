<?php
#$mosimage="{mosimge folder=hallo}";
#$dir = preg_replace('/{mosimage\s*folder=(.*)\s*}/','$1',$mosimage);
#print_r ($dir);
echo "preg_match\n";
$mosimage="{mosimage    folder=[folder1]    title=[ Das ist ein Titel ] random=[1] }";



getMosimageParam($mosimage);

function getMosimageParam($value){
 $DELEMITER='=';

 $param = array();
 preg_match_all('/[a-zA-Z0-9]+' . $DELEMITER . '\[(.*?)\]/', $value, $matches);

  if(!count($matches)) {
    return false;
  }

print_r ($matches);

  foreach($matches[0] as $attr){
      $pieces = explode($DELEMITER, $attr);
      $param[$pieces[0]] = substr($pieces[1],1,-1);
    }

  print_r ($param);
  echo "\n";
}


intvaltest("Hallo");
intvaltest("-1");
intvaltest(-1);

function intvaltest($value){
	echo "[$value] => [". intval($value) ."]\n";

}
?>
