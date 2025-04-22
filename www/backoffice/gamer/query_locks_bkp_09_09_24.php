<?php 
require_once '../../includes/constantes.php';
require_once $raiz_do_projeto."backoffice/includes/topo.php";

  if (isset($_GET["order"])) $order = @$_GET["order"];
  if (isset($_GET["type"])) $ordtype = @$_GET["type"];

  if (isset($_POST["filter"])) $filter = @$_POST["filter"];
  if (isset($_POST["filter_field"])) $filterfield = @$_POST["filter_field"];
  $wholeonly = false;
  if (isset($_POST["wholeonly"])) $wholeonly = @$_POST["wholeonly"];

  if (!isset($order) && isset($_SESSION["order"])) $order = $_SESSION["order"];
  if (!isset($ordtype) && isset($_SESSION["type"])) $ordtype = $_SESSION["type"];
  if (!isset($filter) && isset($_SESSION["filter"])) $filter = $_SESSION["filter"];
  if (!isset($filterfield) && isset($_SESSION["filter_field"])) $filterfield = $_SESSION["filter_field"];

?>

<html>
<head>
<title>query_locks</title>
<meta name="generator" http-equiv="content-type" content="text/html">
<style type="text/css">
  body {
    background-color: #FFFFFF;
    color: #004080;
    font-family: Arial;
    font-size: 12px;
  }
  .bd {
    background-color: #FFFFFF;
    color: #004080;
    font-family: Arial;
    font-size: 12px;
  }
  .tbl {
    background-color: #FFFFFF;
  }
  a:link { 
    color: #FF0000;
    font-family: Arial;
    font-size: 12px;
  }
  a:active { 
    color: #0000FF;
    font-family: Arial;
    font-size: 12px;
  }
  a:visited { 
    color: #800080;
    font-family: Arial;
    font-size: 12px;
  }
  .hr {
    background-color: #336699;
    color: #FFFFFF;
    font-family: Arial;
    font-size: 12px;
  }
  a.hr:link {
    color: #FFFFFF;
    font-family: Arial;
    font-size: 12px;
  }
  a.hr:active {
    color: #FFFFFF;
    font-family: Arial;
    font-size: 12px;
  }
  a.hr:visited {
    color: #FFFFFF;
    font-family: Arial;
    font-size: 12px;
  }
  .dr {
    background-color: #FFFFFF;
    color: #000000;
    font-family: Arial;
    font-size: 12px;
  }
  .sr {
    background-color: #FFFFCF;
    color: #000000;
    font-family: Arial;
    font-size: 12px;
  }
</style>
</head>
<body>
<div class="col-md-12">
    <ol class="breadcrumb top10">
        <li><a href="#" class="muda-aba" ordem="<?php echo $currentAba->getOrdem(); ?>">BackOffice - <?php echo $currentAba->getDescricao();?></a></li>
        <li class="active"><?php echo $sistema->menu[0]->getDescricao(); ?></li>
        <li class="active"><a href="<?php echo $sistema->item->getLink() ; ?>"><?php echo $sistema->item->getDescricao() ; ?></a></li>
    </ol>
</div>
<table width="100%" border="0">
<tr>

<td valign="top">
<?php
  if (!login()) exit;
?>

<?php
  $conn = connect();
  $showrecs = 20000;
  $pagerange = 100000;

  $a = @$_GET["a"];
  $recid = @$_GET["recid"];
  $page = @$_GET["page"];
  if (!isset($page)) $page = 1;

  switch ($a) {
    case "view":
      viewrec($recid);
      break;
    default:
      select();
      break;
  }

  if (isset($order)) $_SESSION["order"] = $order;
  if (isset($ordtype)) $_SESSION["type"] = $ordtype;
  if (isset($filter)) $_SESSION["filter"] = $filter;
  if (isset($filterfield)) $_SESSION["filter_field"] = $filterfield;
  if (isset($wholeonly)) $_SESSION["wholeonly"] = $wholeonly;

  pg_close($conn);
?>
</td></tr></table>

</body>
</html>

<?php function select()
  {
  global $a;
  global $showrecs;
  global $page;
  global $filter;
  global $filterfield;
  global $wholeonly;
  global $order;
  global $ordtype;


  if ($a == "reset") {
    $filter = "";
    $filterfield = "";
    $wholeonly = "";
    $order = "";
    $ordtype = "";
  }

  $checkstr = "";
  if ($wholeonly) $checkstr = " checked";
  if ($ordtype == "asc") { $ordtypestr = "desc"; } else { $ordtypestr = "asc"; }
  $res = sql_select();
  $count = sql_getrecordcount();
  if ($count % $showrecs != 0) {
    $pagecount = intval($count / $showrecs) + 1;
  }
  else {
    $pagecount = intval($count / $showrecs);
  }
  $startrec = $showrecs * ($page - 1);
  if ($startrec < $count) {@pg_result_seek($res, $startrec);}
  $reccount = min($showrecs * $page, $count);
?>
<p>query_locks (<?php echo date("Y-m-d H:i:s").", nrecs: ".$count ?>)</p>
<p>
 - PHP script owner's GID <?php echo getmygid() ?><br>
 - PHP script owner's UID <?php echo getmyuid() ?><br>
 - name of the owner of the current PHP script <?php echo get_current_user() ?><br>
 - inode of the current script <?php echo getmyinode() ?><br>
 - time of last page modification <?php echo date("Y-m-d H:i:s", getlastmod()) ?><br>
</p>

<?php showpagenav($page, $pagecount); ?>

<table class="tbl" border="0" cellspacing="1" cellpadding="5"width="100%">
<tr><td colspan="7"><hr></td></tr>
<tr><td align="center"><b>i</b></td><td align="center"><b>relname</b></td><td align="center"><b>locktype</b></td><td align="center"><b>database</b></td><td align="center"><b>relation</b></td><td align="center"><b>pid</b></td><td align="center"><b>mode</b></td><td align="center"><b>granted</b></td></tr>

<?php
  $n = 1;
  for ($i = $startrec; $i < $reccount; $i++)
  {
    $row = pg_fetch_assoc($res);
//echo "<pre>";
//print_r($row);
//echo "</pre>";

    $style = "dr";
    if ($i % 2 != 0) {
      $style = "sr";
    }
?>
<tr>
<?php
	echo "<td class='".$style."' align='center'>".$n."</td>";
	foreach($row as $key => $val) {
		echo "<td class='".$style."'".(($key!="relname")?" align='center'":"").">".(($key!="granted")?$val:(($val=="t")?"True":"False"))."</td>";
	}
	?>
</tr>
<?php
	$n++;
  }
  pg_free_result($res);
?>
</table>
<br>
<?php showpagenav($page, $pagecount); ?>
<?php } ?>

<?php function login()
{
  global $_POST;
  global $_SESSION;

  global $_GET;

  // log in automatically
  $_SESSION["logged_in"] = true;
  return true;
  exit(0);


  if (isset($_GET["a"]) && ($_GET["a"] == 'logout')) $_SESSION["logged_in"] = false;
  if (!isset($_SESSION["logged_in"])) $_SESSION["logged_in"] = false;
  if (!$_SESSION["logged_in"]) {
    $login = "";
    $password = "";
    if (isset($_POST["login"])) $login = @$_POST["login"];
    if (isset($_POST["password"])) $password = @$_POST["password"];

    if (($login != "") && ($password != "")) {
      if (($login == "postgres") && ($password == "quency15743")) {
        $_SESSION["logged_in"] = true;
    }
    else {
?>
<p><b><font color="-1">Sorry, the login/password combination you've entered is invalid</font></b></p>
<?php } } }if (isset($_SESSION["logged_in"]) && (!$_SESSION["logged_in"])) { ?>
<form action="query_locks.php" method="post">
<table class="bd" border="0" cellspacing="1" cellpadding="4">
<tr>
<td>Login</td>
<td><input type="text" name="login" value="<?php echo $login ?>"></td>
</tr>
<tr>
<td>Password</td>
<td><input type="password" name="password" value="<?php echo $password ?>"></td>
</tr>
<tr>
<td><input type="submit" name="action" value="Login"></td>
</tr>
</table>
</form>
<?php
  }
  if (!isset($_SESSION["logged_in"])) $_SESSION["logged_in"] = false;
  return $_SESSION["logged_in"];
} ?>

<?php function showrow($row, $recid)
  {
?>
<table class="tbl" border="0" cellspacing="1" cellpadding="5"width="50%">
</table>
<?php } ?>

<?php function showpagenav($page, $pagecount)
{
?>
<table class="bd" border="0" cellspacing="1" cellpadding="4">
<tr>
<?php if ($page > 1) { ?>
<td><a href="query_locks.php?page=<?php echo $page - 1 ?>">&lt;&lt;&nbsp;Prev</a>&nbsp;</td>
<?php } ?>
<?php
  global $pagerange;

  if ($pagecount > 1) {

  if ($pagecount % $pagerange != 0) {
    $rangecount = intval($pagecount / $pagerange) + 1;
  }
  else {
    $rangecount = intval($pagecount / $pagerange);
  }
  for ($i = 1; $i < $rangecount + 1; $i++) {
    $startpage = (($i - 1) * $pagerange) + 1;
    $count = min($i * $pagerange, $pagecount);

    if ((($page >= $startpage) && ($page <= ($i * $pagerange)))) {
      for ($j = $startpage; $j < $count + 1; $j++) {
        if ($j == $page) {
?>
<td><b><?php echo $j ?></b></td>
<?php } else { ?>
<td><a href="query_locks.php?page=<?php echo $j ?>"><?php echo $j ?></a></td>
<?php } } } else { ?>
<td><a href="query_locks.php?page=<?php echo $startpage ?>"><?php echo $startpage ."..." .$count ?></a></td>
<?php } } } ?>
<?php if ($page < $pagecount) { ?>
<td>&nbsp;<a href="query_locks.php?page=<?php echo $page + 1 ?>">Next&nbsp;&gt;&gt;</a>&nbsp;</td>
<?php } ?>
</tr>
</table>
<?php } ?>

<?php function showrecnav($a, $recid, $count)
{
?>
<table class="bd" border="0" cellspacing="1" cellpadding="4">
<tr>
<td><a href="query_locks.php">Index Page</a></td>
<?php if ($recid > 0) { ?>
<td><a href="query_locks.php?a=<?php echo $a ?>&recid=<?php echo $recid - 1 ?>">Prior Record</a></td>
<?php } if ($recid < $count - 1) { ?>
<td><a href="query_locks.php?a=<?php echo $a ?>&recid=<?php echo $recid + 1 ?>">Next Record</a></td>
<?php } ?>
</tr>
</table>
<hr size="1" noshade>
<?php } ?>


<?php function viewrec($recid)
{
  $res = sql_select();
  $count = sql_getrecordcount();
  @pg_result_seek($res, $recid);
  $row = pg_fetch_assoc($res);
  showrecnav("view", $recid, $count);
?>
<br>
<?php showrow($row, $recid) ?>
<?php
  pg_free_result($res);
} ?>

<?php 

function connect()
{
        global $host, $port, $banco, $usuario, $senha;
        $conn = pg_connect("host=".DB_HOST." port=".DB_PORT." dbname=".DB_BANCO." user=".DB_USER." password=".DB_PASS);

  return $conn;
}

function sqlstr($val)
{
  return str_replace("'", "''", $val);
}

function sql_select()
{
  global $conn;
  global $order;
  global $ordtype;
  global $filter;
  global $filterfield;
  global $wholeonly;

  $filterstr = sqlstr($filter);
  if (!$wholeonly && isset($wholeonly) && $filterstr!='') $filterstr = "%" .$filterstr ."%";
  $sql = "SELECT pg_class.relname, pg_locks.locktype, pg_locks.database, pg_locks.relation, pg_locks.pid, pg_locks.mode, pg_locks.granted  from pg_class, pg_locks  where pg_class.relfilenode = pg_locks.relation  order by pg_class.relname ";

//echo "$sql<br>";

//  if (isset($filterstr) && $filterstr!='' && isset($filterfield) && $filterfield!='') {
//    $sql .= " where " .sqlstr($filterfield) ." like '" .$filterstr ."'";
//  } elseif (isset($filterstr) && $filterstr!='') {
//    $sql .= " where ";
//  }
//  if (isset($order) && $order!='') $sql .= " order by \"" .sqlstr($order) ."\"";
//  if (isset($ordtype) && $ordtype!='') $sql .= " " .sqlstr($ordtype);
  $res = pg_query($conn, $sql) or die(pg_last_error());
  return $res;
}

function sql_getrecordcount()
{
  global $conn;
  global $order;
  global $ordtype;
  global $filter;
  global $filterfield;
  global $wholeonly;

  $filterstr = sqlstr($filter);
  if (!$wholeonly && isset($wholeonly) && $filterstr!='') $filterstr = "%" .$filterstr ."%";
  $sql = "SELECT COUNT(*) FROM (select pg_class.relname, pg_locks.locktype, pg_locks.database, pg_locks.relation, pg_locks.pid, pg_locks.mode, pg_locks.granted  from pg_class, pg_locks  where pg_class.relfilenode = pg_locks.relation  order by pg_class.relname) subq";
  if (isset($filterstr) && $filterstr!='' && isset($filterfield) && $filterfield!='') {
    $sql .= " where " .sqlstr($filterfield) ." like '" .$filterstr ."'";
  } elseif (isset($filterstr) && $filterstr!='') {
    $sql .= " where ";
  }
  $res = pg_query($conn, $sql) or die(pg_last_error());
  $row = pg_fetch_assoc($res);
  reset($row);
  return current($row);
} ?>
