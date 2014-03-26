<?php
  include 'CHTMLOut.php';
  include 'CFormHandler.php';
  include 'CCookie.php';
  include 'CDoodleTable.php';
  include 'CDataHandler.php';

  define("SPORTCLUB", "Gleitschrim");
  define("ATHLETE", "Pilot");

  $HTML = new HTMLOut();
  $Cookie = new Cookie();
  $DataHandler = new CDataHandler($Cookie->GetUserName());

	$cWeekDays = "Mo,Di,Mi,Do,Fr,Sa,So";
  $cWeekDays = explode(",",$cWeekDays);

  function CleanEntry($Entry) {
   $val = trim($Entry);
   $val = trim($val , ",");
   $val = str_replace(",", ":", $val);
   $val = "," . strip_tags($val);
   $val = substr($val, 0, 100);
   return $val;
  }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  $DataHandler->SetLastAccessAndIncrement();

  if (@$_POST['formLogout'] == "Logout") {
    $Cookie->SetCookie(""); // Clear Cookie
    header("Location: Login.php");
  }
  if (!$Cookie->CheckCookieExists()) {
    header("Location: Login.php");
    exit(0);
  }
  $Editrequest = false;
  if (@$_POST['formEdit'] == "Edit") {
    $Editrequest = true;
  }

  if(@$_POST['formSubmit'] == "Submit") {
      $DataString = $Cookie->GetUserName();
      for ($Day = 1; $Day <= $DataHandler->GetAmountOfDays(); $Day++) {
      	$DataString .= CleanEntry($_POST['ck'.$Day]);
      }
      $DataHandler->EditLine($DataString);
    header("Location: Login.php");
    exit(0);
  }

  $Table = new DoodleTable();
  $Form = new FormHandler("post", "Userform");

  $ActualWeekDay = @date(N);

  // Generate Header with the days
  $Table->AddSingleCell(ATHLETE, CCell::COLOR_HEADER);
  for ($i = $ActualWeekDay; $i <= ($ActualWeekDay + ($DataHandler->GetAmountOfDays() -1)); $i++) {
    $Index = $i % 7;
    $Table->AddSingleCell($cWeekDays[$Index], CCell::COLOR_HEADER);
  }
  $Table->AddRow($Table->GetCellArray(), RowType::Header);


  $UserAlreadyInDatabases = false;
  $LineArray = array();
  while ($DataHandler->GetNextUser($LineArray)) {
		$PrintLine = true;

    if ($LineArray[0] == $Cookie->GetUserName()) {
      $UserAlreadyInDatabases = true;
      if (!$Editrequest) {
        $Form->AddFunctionElement("submit", "formEdit", "Edit");
      }
      else {
      	$PrintLine = false;
        $LineArrayToEdit = $LineArray;
        // Generate Input boxes in case User asked to edit his data
		    GenerateInputboxForEdit($Table, $Form, $Cookie->GetUserName(), $LineArrayToEdit);
      }
    }

    if ($PrintLine) {
	    foreach ($LineArray as $Element) {
	      $Element = trim($Element);
	      if (!empty($Element)) {
	        $Color = CCell::COLOR_YES_I_CAN;
	      } else {
	        $Color = CCell::COLOR_NO_I_CANT;
	      }
				$Table->AddSingleCell($Element, $Color);
	    }
	    $Table->AddRow($Table->GetCellArray());
    }
  }

  if (!$UserAlreadyInDatabases) {
    // Generate Input boxes in case User was not found in the Database
  	GenerateInputboxForEdit($Table, $Form, $Cookie->GetUserName(), "");
  }


  function GenerateInputboxForEdit($Table, $Form, $Cookiename, $LineArrayToEdit)
  {
		$Value = "";
  	$Table->AddSingleCell($Cookiename, CCell::COLOR_YES_I_CAN);
  	for ($Day = 1; $Day <= AMOUNT_OF_DAYS; $Day++) {
			if ($LineArrayToEdit != "") {
				$Value = $LineArrayToEdit[$Day];
			}
  		$Form->AddElement("text", "ck".$Day, $Value);
		}
		$Table->AddMultipleCell($Form->GetElementArrayString(), CCell::COLOR_USER);
		$Table->AddRow($Table->GetCellArray());
    $Form->AddFunctionElement("submit", "formSubmit", "Submit");
  }

  $Form->AddFunctionElement("submit", "formLogout", "Logout");

  //////////////////////////////////////////////////////////////////////////////////////////////
  // Output

  $PageTitle = "Ewiger " . SPORTCLUB . " Doodle";
  echo $HTML->GetHeader($PageTitle);
  echo $HTML->GetPageTitle($PageTitle);
  echo "<h2>You are logged on as <b>". $Cookie->GetUserName() . "</b></h2>\n";

  echo ("<p>You can stay logged on if you want to.</p>\n");
  if (!$DataHandler->GetDataReadResult()) {
  	echo "<p class=\"warning\">Hmmm, datafile not ready! Come back later!</p>";
  }
  echo $Form->StartForm();
  echo $Table->GetTable();
  echo $Form->GetForm();
  echo $Form->GetFunctionElementString();
  $Refreshtext = "refresh";
  if ($Editrequest) { $Refreshtext = "cancle"; }
  echo "<a href=\"\">" . $Refreshtext . "</a>";
	echo $Form->CloseForm();
  echo ("<p class=\"footmsg_l\"><i>Server Date " . @date(r) . "</i></p>");

//   /// Chat
//   $ChatForm = new FormHandler("post", "Chatform");
//   $ChatForm->AddElement("text", "NewChatEntry", "", "Enter Chat Text");
//   $ChatForm->AddFunctionElement("submit", "formSaveChat", "Submit");
//   echo $ChatForm->StartForm();
//   echo $ChatForm->GetForm();
//   echo $ChatForm->GetFunctionElementString();
//   echo $ChatForm->CloseForm();

	echo $HTML->ClosingHtml();

?>