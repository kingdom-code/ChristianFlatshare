<?php
session_start();
    
// Autoloader
require_once 'web/global.php';

connectToDB();

	$pageTitle = "Links and Resources";
	$showForm = TRUE;
	
	$result = mysqli_query($GLOBALS['mysql_conn'], "select count(church_name) from cf_church_directory;");
	$church_count = cfs_mysqli_result($result,0,0);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php print $pageTitle?> - Christian Flatshare</title>
<!-- InstanceEndEditable -->
<link href="styles/web.css" rel="stylesheet" type="text/css" />
<link href="styles/headers.css" rel="stylesheet" type="text/css" />
<link href="css/global.css" rel="stylesheet" type="text/css" />
<link href="favicon.ico" rel="shortcut icon"  type="image/x-icon" />
	<!-- jQUERY -->
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript">
    //no conflict jquery
    jQuery.noConflict();
</script>
<!-- MooTools -->
<script language="javascript" type="text/javascript" src="includes/mootools-1.2-core.js"></script>
	<script language="javascript" type="text/javascript" src="includes/mootools-1.2-more.js"></script>
	<script language="javascript" type="text/javascript" src="includes/icons.js"></script>
<!-- InstanceBeginEditable name="head" --><!-- InstanceEndEditable -->
<!-- InstanceParam name="mainContentClass" type="text" value="" -->
</head>

<body>
<div id="canvas">
    <?php print $theme['superheader']; ?>
	<div id="content">
		<?php print $theme['header']; ?>
		<div class="" id="mainContent">
		<!-- InstanceBeginEditable name="mainContent" -->
			
		<div id="header_resources" class="header">
			<h1>Links and Resources </h1>
			<h2>helpful and fun links that we think may be of interest to you... </h2>		
		</div>
			<div style="width:400px; float:left;">
				<p class="mt0">Below are listed links to websites that we think may be of interest to you.<br />
				For links to Christian Flatshare supporting churches, see <a href="churches-using-cfs.php?area=Greater%20London#directory" target="_blank">CFS church directory</a>.</p>
				<p class="mt0">
				  If there is a link which you think would be helpful for us to include here, please <a href="contact-us.php" target="_blank">contact us</a> and let us know, (please see guidance below).</p>
			  <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
            <td style="padding-top:0px;font-size:19px; font-weight:bold; color:#990000; line-height:normal;">Accommodation</h1_links></td>
          </tr>				
          <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.affordablechristianhousing.org/" target="_blank">Affordable Christian Housing</a></span> - Affordable Christian Housing is a charitable housing association operating in London, and which committed to helping key workers of London churches by providing them with housing as a way to strengthen churches and Christian witness in London.</td>
          </tr>
					
          <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.leeabbeylondon.com/" target="_blank">Lee Abbey Student Accommodation, London</a></span> -  Lee Abbey has a range of accommodation to suit different budgets and needs, for short or for long stays, from triple rooms through to en-suite single rooms. Located in a quiet and exclusive residential part of Kensington near Holland Park and Kensington Gardens, Lee Abbey is within easy reach of most central London colleges.</td>
          </tr>
     </table>	
								
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
      <td style="padding-top:8px;font-size:19px; font-weight:bold; color:#990000; line-height:normal;">Students</h1_links></td>
			</tr>
        <tr>
           <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.fusion.uk.com" target="_blank">Fusion</a></span> - Fusion is passionate about student mission, serving churches, working with students and developing student workers.</td>
          </tr>
										
          <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.uccf.org.uk" target="_blank">UCCF: the Christian Unions </a></span>- Christian Unions are mission teams that operate at the very heart of university and college campuses. Led by students, resourced by Christian Union Staff Workers and supported by the local church. Our call is to partner with God in His rescue mission to students.</td>
          </tr>
        </table>	
				
	  <table width="100%" border="0" cellpadding="0" cellspacing="0">
     <tr>
            <td style="padding-top:8px;font-size:19px; font-weight:bold; color:#990000; line-height:normal;">Everyone</td>
          </tr>
          <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.christianvocations.org"  target="_blank">Christian Vocations</a></span> - Christian Vocations exists to resource and challenge Christians to discover and practise their God-given vocation and mobilise them for His service. They provide services to enable individuals to reflect on personal gifting and calling, and provide resources to motivate as well as providing information on thousands of opportunities to serve in the UK or around the world.</td>
          </tr>
          <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.findachurch.co.uk"  target="_blank">Find a Church</a></span> - Our purpose is to help people get involved in a local church where they will feel welcome and can become actively involved in the church's life and activities, deepening their faith and exercising their natural and spiritual gifts.</td>
          </tr>		
          <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.oakhall.co.uk"  target="_blank">Oak Hall expeditions</a></span> - Oak Hall expeditions aims to enable their participants to reach new heights their spiritual lives as Christian friendship is shared and evening Bible study talks are enjoyed. Winter and Summer expedition trips, and "A Taste Of Mission" expeditions which provide an insight into what is being done in different parts of the world to share the Good News of Jesus with others.</td>
          </tr>										
        </table>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td style="padding-top:8px;font-size:19px; font-weight:bold; color:#990000; line-height:normal;">Sport</td>
          </tr>
       <!--   <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.christiansinsport.org.uk"  target="_blank">Christians in Sport</a></span> - Christians in Sport is a missionary organisation with a vision to see Christians in every sports club, representing Christ and building the Church. </td>
          </tr> -->
          <tr>
            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.christiansurfers.co.uk"  target="_blank">Christians Surfers UK</a></span> -  we see God&rsquo;s signature written all over his creation. Our desire is to experience this amazing God for ourselves through a personal relationship with his son Jesus Christ. Our aim is therefore a simple one: to be a Christian presence and witness in the Surfing Community throughout the UK; and to tell other surfers that they too can have a relationship with Jesus Christ.</td>
          </tr>					
					
        </table style="border-spacing: 10px;">				
				<br />				
			</div>
				<div id="columnSeparator" style="width:50px; height:500px;"><!----></div>
			<div style="width:400px; float:left;">
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td style="font-size:19px; font-weight:bold; color:#990000; line-height:normal;">Something for the Summer?</td>
          </tr>
					
          </tr>
          <tr>            <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.creationfest.org.uk/"  target="_blank">Creation Fest</a></span> - <strong>6-12 August, 2016 - Woolabcombe, Devon</strong><br />
                      Creation Fest is a FREE Christian music festival, simply turn up and enjoy the awesome line up of bands. Creation Fest also includes teaching seminars, workshops, a massive skate park, sports and family activities - all within easy access to one of the UK's best surf beaches!
</td>
          </tr>		
          </tr>
          <tr>              <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.greenbelt.org.uk/"  target="_blank">Greenbelt</a></span> - <strong>26-29 August, 2016 - Cheltenham Racecourse</strong><br />
                Greenbelt is Christian music and arts festival which expresses love, creativity and justice in the arts and contemporary culture in the light of the Christian gospel.</td>
          </tr>				
															
    <tr>        <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://momentum.org.uk/"  target="_blank">Momentum</a></span> - <strong>22-26 July 2016 - Bath and West Showground</strong><br />
          <tr>
              Momentum is a five day event, aimed at students and those in their twenties. The event has a really strong Christian flavour, with main meetings and a seminar programme that attempt to help us work out how to follow Jesus throughout our whole lives.  </td>
          </tr>
		  <tr>
                        <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://wordaliveevent.org/"  target="_blank">New Word Alive</a></span><strong>
						<br />- 2-7 April 2016</strong><br />
						New Word Alive is an opportunity to come together as individuals, as families and as God's Church to focus on Christ.</td>
          </tr>						  
         <tr>               <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.new-wine.org/"  target="_blank">New Wine</a></span><strong>
						<br />- 23 July - 29 July 2016 Somerset 
						<br />- 31 July - 6 August 2016  Somerset </strong><br />
						New Wine&rsquo;s vision is to see the nation changed through Christians and churches being filled with the Spirit, alive with the joy of knowing and worshipping Jesus Christ, living out his Word, and doing the works of the Kingdom of God. Join the summer conferences to pray and work with us towards God&rsquo;s kingdom coming to earth, just as Jesus taught us.</td>
          </tr>				
		
        </table>				
	<table width="100%" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td style="padding-top:8px;font-size:19px; font-weight:bold; color:#990000; line-height:normal;">Volunteering </td>
          </tr>
					
          </tr>
                    <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.cpas.org.uk/ventures/content/ventures%20and%20falcon%20camps_560.php?e=745"  target="_blank">Volunteering on a Venture (CPAS)</a></span> - <strong>Summer, Nationwide</strong><br />
                    Venture holidays are life changing holidays for 8-18 year olds. Each year around 100 Ventures take place all over the UK on which thousands of young people come to meet new friends, take part in exciting activities, chill out and learn more about God. Ventures are run by thousands of carefully selected volunteers to provide these life-changing holidays for 8-18s. <br />We&rsquo;re looking for leaders who are passionate about enabling young people and children to meet Jesus Christ, get to know him better, and in turn lead others to him. If you are over 18 you could make a massive difference to someone&rsquo;s life by helping run a Venture.
</td>
          </tr>		
				
          </tr>
                      <td style="padding-top:5px;padding-bottom:5px"><span style="font-size:14px; font-weight:bold;"><a href="http://www.oscar.org.uk"  target="_blank">OSCAR</a></span> - <strong>World-wide</strong><br />
                    OSCAR, the UK Information Service for World Mission. OSCAR provides advice, information and resources for anyone involved or interested in cross-cultural mission work.<br />
                    As well as lots of practical advice on every aspect of mission, there are also hundreds of opportunities from a broad range of Christian organisations. OSCAR has a mission-focused social network called <a href="http://oscaractive.ning.com" target="_blank">OSCARactive</a>.</td>
          </tr>						
	
        </table>						
 	<h3 class="mb5 grey">Christian Flatshare links</h3>
						<p class="mt0 grey" >Christian Flatshare recognises that it plays its part among and alongside many other Christian ministries, all of whom are helping to build the Kingdom and the body of Christ, in the ways that they are called to. As such we wish to grow this page to be a helpful and encouraging resource for our visitors, and to generously support those church ministries whose audience overlaps with Christian Flatshare's.</p>
            <p class="grey" >Those websites and organisations which are easiest for us to link to include those that are of interest nationally, operate on a not-for-profit basis and that are likely to be supported by the 
            <?php print  $church_count ?> churches and Christian organisations who have given their support to Christian Flatshare since January 2007.  </p>						
			</div>
			<div class="cc0"><!----></div>
		<p><a href="#" onclick="history.go(-1);">Return to the previous page</a>        </p>
		<!-- InstanceEndEditable -->
		</div>
		<div class="redMenu">
			<ul>
				<!--<li><a href="../flat-finding-tips.php">flat finding tips</a></li>-->
				<li><a href="advertising.php">advertising</a></li>			
				<li><a href="where-does-all-the-money-go.php">where does the money go?</a></li>
				<li><a href="glossary.php">glossary</a></li>
				<li><a href="terms-and-conditions.php">terms &amp; conditions</a></li>
				<li><a href="privacy-policy.php">privacy policy</a></li>
				<li><a href="contact-us.php">contact us</a></li>
				<li class="noSeparator"><a href="resources.php">links &amp; resources</a></li>
			</ul>
		</div>
		<div id="footerText">
			<p class="m0"><strong>Christian Flatshare... helping accommodation seekers connect with the local church community<br />
			Finding homes, growing churches and building communities </strong>&copy; ChristianFlatShare.org 2007-<?php print date("Y")?></p>
	  </div>
	</div>
	<div id="footer"><img src="images/spacer.gif" alt="*" width="1" height="12"/></div>
</div>
<?php if (DEBUG_QUERIES && $debug) { echo sprintf(DEBUG_FORMAT,$debug); }?>
<?php print getTrackingCode();?>
</body>
<!-- InstanceEnd --></html>
