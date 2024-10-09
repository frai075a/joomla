<?php 
defined( '_JEXEC' ) or die( 'Restricted Access' ); 
use Joomla\CMS\Plugin\CMSPlugin; 
use Joomla\Database\DatabaseDriver; 
use Joomla\Database\ParameterType;
use Joomla\CMS\User\User;
class plgContentWerkickt extends CMSPlugin { 
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
    protected $db2;                    
  
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

            $query = "
                     WITH
                         Buchungen AS(
                         SELECT
                             a.datum,
                             a.teilnehmer,
                             a.Zusage,
                             a.created_by,
                             a.created_when,
                             CASE WHEN WEEKDAY(a.datum) = 0 THEN 'Mo., '
                                  WHEN WEEKDAY(a.datum) = 1 THEN 'Di., '
								  WHEN WEEKDAY(a.datum) = 2 THEN 'Mi., ' 
								  WHEN WEEKDAY(a.datum) = 3 THEN 'Do., ' 
								  WHEN WEEKDAY(a.datum) = 4 THEN 'Fr., ' 
								  WHEN WEEKDAY(a.datum) = 5 THEN 'Sa., ' 
								  WHEN WEEKDAY(a.datum) = 6 THEN 'So., ' 
								  ELSE ''
                     END AS wochentag
                     FROM
                         #__ttc_hallenfussball a
                     WHERE
                         datum >= CURDATE()
                     ORDER BY
                         datum, zusage, created_when)
                     SELECT
                         Buchungen.*,
                         hi.information
                     FROM
                         Buchungen
                     LEFT JOIN #__ttc_halleninfos hi ON
                         Buchungen.datum = hi.datum
                     WHERE
                         Buchungen.datum IS NOT NULL
                     UNION ALL
                     SELECT
                         hi.datum,
                         '' AS teilnehmer,
                         '' AS Zusage,
                         0 AS created_by,
                         CURRENT_TIMESTAMP AS created_when,
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
                     FROM
                         Buchungen
                     RIGHT JOIN #__ttc_halleninfos hi ON
                         Buchungen.datum = hi.datum
                     WHERE
                         Buchungen.datum IS NULL AND hi.datum >= CURDATE()
                     ORDER BY
                         datum, zusage, created_when;         
                     ";                 
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
         
            $datum_alt = '';
            $Zusage_alt = '';
  
            foreach ($rows as $myrow) 
            {
                $datum_umw = substr($myrow->datum, 8, 2).".".substr($myrow->datum, 5, 2).".".substr($myrow->datum, 0, 4);
                if ($datum_alt != $datum_umw) {
                    if ($olcounter == 1)
                    {
                        $html .= "</ol>\n";
                        $olcounter--;
                    }
                    $html .= "<span style='font-weight:bold'>".$datum_umw."</span><br>\n";
                    $Zusage_alt = '';
					if ($myrow->information != NULL) {
					   $html .= "<span style='font-style:italic'>".$myrow->information."</span>\n";
				       $html .= "<br><br>\n";
					}
					
                }
                if ($Zusage_alt != $myrow->Zusage) {
                    if ($olcounter == 1) 
                    {
                        $html .= "</ol>\n";
                        $olcounter--;
                    }
                    if ($myrow->teilnehmer != NULL) {
						// $myrow->teilnehmer <> '' ist der Indikator, dass Buchungen vorhanden sind und nicht nur Halleninfos
                  
                        if ($myrow->Zusage == 'ja') 
                            $ZuAbsage='Zusage:';
                        else 
                            $ZuAbsage='Absage:'; 
                
                        $html .= "<span style='text-decoration:underline'>".$ZuAbsage."</span><br>\n";
                        $html .= "<ol class='list-number-bollet bollet-blue'>\n";
                        $olcounter++;
					}
                }
                $datum_alt = $datum_umw;
                $Zusage_alt = $myrow->Zusage;
                if ($myrow->teilnehmer != NULL) {
				    $html .= "<li>".$myrow->teilnehmer."</li>\n";
			    }

            }
            if ($olcounter == 1)
            {
               $html .= "</ol>\n";
               $olcounter--;
            }

          
      
        $regex = "/{werkickt}/s";
        $row->text = preg_replace($regex, $html, 
                                            $row->text); 
        return true;     
    }
}