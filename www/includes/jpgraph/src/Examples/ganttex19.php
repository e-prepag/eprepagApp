<?php
include ("../jpgraph.php");
include ("../jpgraph_gantt.php");

$graph = new GanttGraph(0,0,"auto");
$graph->SetBox();
$graph->SetShadow();

// Use swedish locale
//$graph->scale->SetDateLocale("sv_SE");
$graph->scale->SetDateLocale("");

// Add title and subtitle
$graph->title->Set("Example of captions");
$graph->title->SetFont(FF_ARIAL,FS_BOLD,12);
$graph->subtitle->Set("(ganttex19.php)");

// Show day, week and month scale
$graph->ShowHeaders(GANTT_HDAY | GANTT_HWEEK | GANTT_HMONTH);

// Set table title
$graph->scale->tableTitle->Set("(Rev: 1.22)");
$graph->scale->tableTitle->SetFont(FF_FONT1,FS_BOLD);
$graph->scale->SetTableTitleBackground("silver");
$graph->scale->tableTitle->Show();

$graph->scale->divider->SetStyle('solid');
$graph->scale->divider->SetWeight(2);
$graph->scale->divider->SetColor('black');


$graph->SetBox(true,"navy",2);


// Use the short name of the month together with a 2 digit year
// on the month scale
$graph->scale->month->SetStyle(MONTHSTYLE_SHORTNAMEYEAR2);
$graph->scale->month->SetFontColor("white");
$graph->scale->month->SetBackgroundColor("blue");

// 0 % vertical label margin
$graph->SetLabelVMarginFactor(1);

// Format the bar for the first activity
// ($row,$title,$startdate,$enddate)
$activity = new GanttBar(0,"Project","2001-12-21","2002-01-07","[50%]");

// Yellow diagonal line pattern on a red background
$activity->SetPattern(BAND_RDIAG,"yellow");
$activity->SetFillColor("red");

// Set absolute height
$activity->SetHeight(10);

// Specify progress to 60%
$activity->progress->Set(0.6);
$activity->progress->SetPattern(BAND_HVCROSS,"blue");

// Format the bar for the second activity
// ($row,$title,$startdate,$enddate)
$activity2 = new GanttBar(1,"Project","2001-12-21","2002-01-02","[30%]");

// Yellow diagonal line pattern on a red background
$activity2->SetPattern(BAND_RDIAG,"yellow");
$activity2->SetFillColor("red");

// Set absolute height
$activity2->SetHeight(10);

// Specify progress to 30%
$activity2->progress->Set(0.3);
$activity2->progress->SetPattern(BAND_HVCROSS,"blue");

// Finally add the bar to the graph
$graph->Add($activity);
$graph->Add($activity2);

// Add a vertical line
$vline = new GanttVLine("2001-12-24","Phase 1");
$vline->SetDayOffset(0.5);
//$graph->Add($vline);

// ... and display it
$graph->Stroke();
?>
