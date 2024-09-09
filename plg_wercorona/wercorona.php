<?php 
defined( '_JEXEC' ) or die( 'Restricted Access' ); 
use Joomla\CMS\Plugin\CMSPlugin; 
use Joomla\Database\DatabaseDriver; 
use Joomla\Database\ParameterType;
use Joomla\CMS\User\User;
class plgContentwercorona extends CMSPlugin { 
	/**                               
	 * Application object             
	 *                                
	 * @var    CMSApplicationInterface
	 * @since  4.0.0                  
	 */                               
	protected $app;                   
                                    
	/**                               
	 * Database Driver Instance       
	 *                                
	 * @var    DatabaseDriver         
	 * @since  4.0.0                  
	 */                               
	protected $db;                    
  
    function __construct(& $subject, $config)
    {
            parent::__construct( $subject, $config ); 
            $this->loadLanguage();
     } 

	function onContentPrepare ($context, &$row, &$params, $page = 0)
	{
            $rowcounter = 0;
            $olcounter = 0;
            $app = $this->app;	        
            $db = $this->db;
            $html = '';
          //  $user = $this->user; 

            $query = "WITH Buchungen AS
                     (
                       SELECT a.datum as Termin,
                              a.teilnehmer,
                        	   a.slot,
                       	   a.id,
                       	   'tt' as purpose,
                       	   coalesce(anz, 0) as spiele,
                            CASE WHEN WEEKDAY(a.datum) = 0 THEN 'Mo., '
                       	        WHEN WEEKDAY(a.datum) = 1 THEN 'Di., '
                       	        WHEN WEEKDAY(a.datum) = 2 THEN 'Mi., '
                       		    WHEN WEEKDAY(a.datum) = 3 THEN 'Do., '
                       		    WHEN WEEKDAY(a.datum) = 4 THEN 'Fr., '
                       		    WHEN WEEKDAY(a.datum) = 5 THEN 'Sa., '
                       		    WHEN WEEKDAY(a.datum) = 6 THEN 'So., '					 
                       		    ELSE ''
                       	   END AS wochentag
                       FROM #__ttc_coronaorga_ a 
                       LEFT JOIN (SELECT datum,
                                         count(*) as anz
                         		   from #__ttc_spielplan
                       		   WHERE heimmannschaft like 'TTC Nordend%' GROUP BY datum) c
                       ON a.datum = c.datum
                       WHERE a.datum >= CURRENT_DATE
                       UNION ALL
                       SELECT b.datum as Termin,
                              b.teilnehmer as Paarung, 
                              CASE WHEN WEEKDAY(b.datum) = 2 THEN 2
                                   WHEN WEEKDAY(b.datum) = 4 THEN 1
                       	          WHEN WEEKDAY(b.datum) = 5 THEN 1
                       	          ELSE 0
                              END AS slot,
                              b.id,
                       	   'fb' as purpose, 
                       	   0 as spiele,
                              CASE WHEN WEEKDAY(b.datum) = 0 THEN 'Mo., '
                                   WHEN WEEKDAY(b.datum) = 1 THEN 'Di., '
                       	          WHEN WEEKDAY(b.datum) = 2 THEN 'Mi., '
                       	          WHEN WEEKDAY(b.datum) = 3 THEN 'Do., '
                       	          WHEN WEEKDAY(b.datum) = 4 THEN 'Fr., '
                       	          WHEN WEEKDAY(b.datum) = 5 THEN 'Sa., '
                       	          WHEN WEEKDAY(b.datum) = 6 THEN 'So., '					 
                       	          ELSE ''
                              END AS wochentag
                       FROM #__ttc_hallenfussball b
                       WHERE b.datum >= CURDATE( ) and b.zusage = 'ja' 
                     )
                     SELECT Buchungen.*, hi.information FROM Buchungen 
                     LEFT JOIN
                      #__ttc_halleninfos hi
                     ON Buchungen.Termin = hi.datum
                     WHERE Buchungen.Termin IS NOT NULL
                     UNION ALL
                       SELECT
                             hi.datum,
                             '' as Paarung, 
                             0  AS slot,
                             0  as id,
                       	     '' as purpose, 
                       	     0  as spiele,
                             CASE WHEN WEEKDAY(hi.datum) = 0 THEN 'Mo., '
                                  WHEN WEEKDAY(hi.datum) = 1 THEN 'Di., '
                       	          WHEN WEEKDAY(hi.datum) = 2 THEN 'Mi., '
                       	          WHEN WEEKDAY(hi.datum) = 3 THEN 'Do., '
                       	          WHEN WEEKDAY(hi.datum) = 4 THEN 'Fr., '
                       	          WHEN WEEKDAY(hi.datum) = 5 THEN 'Sa., '
                       	          WHEN WEEKDAY(hi.datum) = 6 THEN 'So., '					 
                       	          ELSE ''
                             END AS wochentag,
                              hi.information
                      FROM Buchungen 
                     RIGHT JOIN
                      #__ttc_halleninfos hi
                     ON Buchungen.Termin = hi.datum
                     WHERE Buchungen.Termin IS NULL
					   AND hi.datum >= CURDATE( )
                     
                     ORDER BY 1, 3, 4"; 
            $db->setQuery($query);
            
            try
            {
            	   $rows = $db->loadObjectList();
            } 
  		 		  catch (RuntimeException $e)
        		{
		 	  	    $this->app->enqueueMessage($e->getMessage(), 'error');
					    return false;
				    }

             
             
            $Termin_alt = '';
            $slot_alt = '';
//var_dump($rows);      
            foreach ($rows as $myrow) 
            {
              $Termin_umw = substr($myrow->Termin, 8, 2).".".substr($myrow->Termin, 5, 2).".".substr($myrow->Termin, 0, 4);

		    	    if ($myrow->spiele == "0") {
//		    	          	 $freie_plaetze = 14;
									$freie_plaetze = 18;
		    	        $anzstring = "kein";
   		    		} elseif ($myrow->spiele == "1") {
//   		    	  	$freie_plaetze = 6;
                       $freie_plaetze = 18;
   		    		         $anzstring = "1";
     		      	       } else {
//     		      	      		$freie_plaetze = 0;
                       $freie_plaetze = 18;
     		      	      		$anzstring = "mehr als ein ";
     		      	      }
     		      	    $freie_plaetze_print = $freie_plaetze;
                    if ($Termin_alt != $Termin_umw)
                    {
                        if ($olcounter == 1)
                        {
                            $html .= "</ol>";
                            $olcounter--;
                        }
                        $html .= "<br>";
                        $tag_str=$myrow->wochentag; //getwochentag(myrow->Termin);
						$html .= "<span style='font-weight:bold'>".$tag_str.$Termin_umw."</span><br>";
//                        $html .= "<span style='font-style:italic'>".$freie_plaetze_print." Anmeldung pro Slot m&oumlglich</span><br>";
//                        $html .= "<span style='font-style:italic'>".$freie_plaetze_print." Anmeldungen gesamt m&oumlglich, da ".$anzstring." Heimspiel</span><br>";
                        $slot_alt = '';
						if ($myrow->information != NULL)
							$html .= "<span style='font-style:italic'>".$myrow->information."</span><br>";
                        $html .= "<br>";
                    }
					$tag=date('w',strtotime($myrow->Termin));
                    if ($slot_alt != $myrow->slot AND $myrow->slot > 0 )
                    {
                        if ($olcounter == 1) 
                        {
                            $html .= "</ol>";
                            $olcounter--;
                        }
      				
                        if ($myrow->slot == '1') 
							if ($myrow->wochentag != 'Sa., ') //SQL-WEEKDAY 5 = Samstag
								$Slotzeit = '19:00 - 20:30 Uhr';
							else
								$Slotzeit = '14:00 - 15:30 Uhr';
						else
							if ($myrow->wochentag != 'Sa., ') //SQL-WEEKDAY 5 = Samstag
								$Slotzeit = '20:30 - 22:00 Uhr';
							else
								$Slotzeit = '15:30 - 18:00 Uhr';

						
						if ($myrow->purpose == 'tt')
							$Sportart = ' (TT)';
						else
							$Sportart = ' (Fussball)';
						$Slottext=$Slotzeit.$Sportart;
                        $html .= "<span style='text-decoration:underline'>".$Slottext."</span><br>\n";      			
                        if ($myrow->purpose == 'tt')  
			                    $html .= "<ol class='list-number-bullet bullet-blue'>" ;                        
			                  else
			                    $html .= "<ol class='list-number-bullet bullet-green'>" ;                        
                        $olcounter++;
                        $rowcounter=0;
                    }
                    else
                       if ($rowcounter == $freie_plaetze)
		   									   $html .= "</ol><span style='font-style:italic'>Warteliste:</span><br><ul class='list-number-bullet bullet-red'>" ;
			                  
                    //$alttext="Klicken um Eintrag zu löschen. Eintrag stammt von ".$myrow->Vorname." ".$myrow->Name;
                    $Termin_alt = $Termin_umw;
                    $slot_alt = $myrow->slot;
                    if ($myrow->slot > 0) {
						// $myrow->slot > 0 ist der Indikator, dass Buchungen vorhanden sind und nicht nur Halleninfos
						$html .= "<li>".$myrow->teilnehmer."</li>";
						$rowcounter++;
					}
            	  }
            	  if ($rowcounter > 0)
            	  	$html .= "</ol>";
          
      
        $regex = "/{wercorona}/s";
        $row->text = preg_replace($regex, $html, $row->text); 
     
		return true;     
	}

}