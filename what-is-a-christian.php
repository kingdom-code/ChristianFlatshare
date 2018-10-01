<?php
session_start();

// Autoloader
require_once 'web/global.php';

$debug = NULL;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/final-1024.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>What is a Christian? - Christian Flatshare</title>
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
			<div id="header_what_is_a_christian" class="header">
				<h1>What is a Christian?</h1>
			</div>
			
	<script language="javascript" type="text/javascript">
		
		var slide = new Array();
		window.addEvent('domready', function(){
			
			// TAB FUNCTIONALITY
			// Get all links inside tablist_2
			$$('#tablist_2 a').each(function(a){
			
				// Assing an on click handler to this link
				a.addEvent('click',function(){
				
					// Reset all links to their original state
					$$('#tablist_2 a').removeClass('selected');
					// Hide all tabs
					$$('div.newtab').setStyle('display','none');
					
					// Set the current link to the UP state
					this.addClass('selected');
					// Show the appropriate tab
					var id = this.id.substr(5);
					$(id).setStyle('display','');				
				
				});
			
			
			});
			
			
			// TAB #1 (faq sliding panes)		
			// Get all links on the page that have a class of "paneSwitch" and
			// assign the "pane open / close" functionality to them
			$$('a.paneSwitch').each(function(a){
				// Assign a slide transition to each faq div
				slide[a.id] = new Fx.Slide("pane_"+a.id,{transition: Fx.Transitions.Quad.easeOut});
				// Initially have each answer div hidden
				slide[a.id].hide();						
				// add an event listener to all links to show the answer divs
				a.addEvent('click',function(e) {
					e = new Event(e);
					slide[this.id].toggle();
					e.stop();
				});
			});
		});

	</script>
			
			<div id="tablist_2">
				<ul>
					<li style="margin-left:15px;"><a href="#" class="selected" id="link_tab_1"><div><div>What is a Christian?</div></div></a></li>
					<li><a href="#" id="link_tab_2"><div><div>Why follow Jesus?</div></div></a></li>
					<li><a href="#" id="link_tab_3"><div><div>How do I become a Christian?</div></div></a></li>
					<li><a href="#" id="link_tab_4"><div><div>Finding out more</div></div></a></li>					
				</ul>				
			</div>



			<div class="newtab" id="tab_1">
			<a name="2" id="2"></a><h2>What is a Christian?</h2>
			<div style="width:400px; float:left;">
				<p class="mt0">A Christian is one whose life is centred on Jesus Christ.<br /> A Christian is one who is part of a  great adventure.</p>
				<p class="mt0">In short a Christian is one who has come to  see that in and through the life, death, resurrection and ascension of Jesus  Christ, God the Father has acted decisively to put the world to rights. In  believing this, in the sense of trusting or putting the weight of one&rsquo;s  existence on something, the Christian is related, by the Holy Spirit, to Jesus  Christ in such a way that they begin a new way of living.</p>
				<p>This way of living is marked by faith, hope and  love. It leads to a more truly human life as the Christian seeks in the power  of the Spirit to grow like Christ in his character and ways of relating to God  the Father, each other and the whole created order.</p>
			</div>
			<div class="cs" style="width:50px; height:220px;"><!----></div>
			<div style="width:400px; float:left;">
				<p class="mt0">A Christian, on entering this new reality,  is incorporated into God's people, the church, and joins in the adventure of  participating in God's rescuing purposes played out in the whole of creation.</p>
				<p class="mt0"> The Christian's supreme concern is to be  obedient to Jesus Christ and to glorify or foster the reputation of God in the  sight of all people. This means that, in identifying a Christian, one would  hope to find someone who not only acknowledges their belief but demonstrates a  concern for justice and, in particular, for the poor.</p>
				<p class="mt0">No Christian is perfect and the great message of  God's rescuing forgiveness fosters an appropriate humility and releases the  resources to keep going in learning to live the new life faithfully and as fully as may be.</p>
			</div>
			<div class="cc0"><!----></div>
			<div class="hr"><!----></div>
			
			
				
			</div>
			
			<div class="newtab" id="tab_2" style="display:none;">
			<a name="1" id="1"></a><h2>Why follow Jesus?</h2>
			<div style="width:400px; float:left;">
			<p class="mt0">Here are six amazing things about living life with a Christian faith and following Jesus:</p>
<p class="mt0"><strong>1. Experience a fulfilled life of true freedom</strong><br />
			  			  Unlike the popular misconception that  Christianity is boring and irrelevant, following Jesus is anything but boring.  How can following someone who turns water into wine, hangs out with the  oppressed, the marginalized and the poor, heals people and raises people from  the dead be boring? Some might be of the opinion that being a Christian means  following a whole bunch of rules that God has designed merely to rob us of  freedom. Instead, God shows us, supremely in Jesus Christ, how to live life to  the full. He shows us a way of living life whereby we can find true fulfilment  and a way that does not hurt other people. It is a life of true freedom within boundaries. Imagine a  football game without rules (boundaries). Players would be &ldquo;free&rdquo; to do what  they like, but it would be chaos not fun. Only with good rules will the players  be free to live up to their true potential, relate to the other players and really enjoy themselves. In Jesus, God demonstrates true wisdom for living life  to its fullest.</p>
				<p><strong>2. Experience a personal relationship with the living God</strong><br />
			  Being a Christian is about being in a  living relationship with God, the Creator of the whole universe. How  awesome is that! The Creator of the whole universe is mindful of you; He cares  about you; He loves you and He wants to be reconciled to you in a personal  relationship. He wants you to join him in a great adventure.</p>
				<p><strong>3. Experience a life of meaning and purpose</strong><br />Being a Christian is about coming to the  profound realisation that we did not merely evolve by chance, but rather we  were designed by God for a purpose; to be in a relationship with God and  to reflect His image (i.e. reflect God&rsquo;s love, compassion and justice). God&rsquo;s  image is supremely seen in Jesus Christ. A Christian&rsquo;s vocation is, therefore,  to follow Jesus and reflect his character. Just like Jesus came to serve others  rather than being served, a Christian&rsquo;s life is less focused on oneself and  more focused on bring God&rsquo;s love, compassion, justice and good news of  reconciliation to a hurting world.</p>
			</div>
			<div class="cs" style="width:50px; height:540px;"><!----></div>
			<div style="width:400px; float:left;">
				<p class="mt0"><strong>4. Experience profound forgiveness</strong><br />Through being reconciled into a right relationship with God,  Christians experience complete forgiveness, and will be set free from  all guilt. This is similar to being reconciled to someone after an argument,  when there has been awkwardness, guilt and regret. Yet the forgiveness that  Christians find in God is even greater; it will be as though they had never  wronged God: totally forgiven, completely restored and absolutely no guilt. Is  there anything you would like to be forgiven for? A slate you would like to  wipe clean?</p>
				<p><strong>5. Experience a life characterised by hope</strong><br />Being a Christian is about living with a  very real hope. For the fear of death is replaced with the hope of  eternal life in heaven, where we will be in a perfect relationship with God.  There will be no more sin, no more hunger, no more thirst, no more mourning, no  more crying and no more pain. </p>
				<p><strong>6. Experience a new start</strong><br />Being a Christian is about being made new.  It is about having a fresh start. It is about changing for the good. Unlike the  popular misconception that people cannot change, a Christian is someone who is  in the process of being set free from old and destructive habits and is forming  new habits, such as: love, joy, peace, patience, kindness, goodness,  faithfulness, gentleness and self-control. For on this adventure with God we  slowly become more and more like our Creator, like a child reflecting their  parent&rsquo;s character. </p>
				<p>&nbsp;All  of this, and much more, is only possible because of God&rsquo;s gracious love  demonstrated in Jesus' life, death, resurrection and ascension, and because of  the gift of the Holy Spirit. Therefore, Christians&rsquo; lives are also marked with  great humility and thankfulness to their Creator and Saviour, God the Father,  Son and Holy Spirit.</p>
				Would you like to join God in this great  adventure? To start again? Some say that coming to faith is like always  thinking the world was flat, and then realising it's round.			</div>
			<div class="cc0"><!----></div>
			<div class="hr"><!----></div>

			</div>
			


			<div class="newtab" id="tab_3" style="display:none;">
			<a name="3" id="3"></a><h2>How do I become a Christian?</h2>
			<div style="width:400px; float:left;">
				<p class="mt0">Through Jesus' life, death, resurrection and ascension, God has decisively put the world to rights. Why did the world need fixing? Ultimately because humanity had turned their backs on a  relationship with God and followed their own self-centred desires, rather than doing God&rsquo;s will (the Bible calls this sin). The consequence of humanity&rsquo;s  dysfunctional relationship with God was that all people were alienated from  God.<sup><a href="#fn_1">i</a></sup></p>
				<p>This alienation resulted in evil, decay and ultimately death prevailing in the  world.<sup><a href="#fn_2">ii</a></sup> But then, because God loved the world so much,<sup><a href="#fn_3">iii</a></sup> God reconciled himself to the world through Jesus.<sup><a href="#fn_4">iv</a></sup> It was as if God said, &quot;lay all the blame on me. Lay all the consequences of  this dysfunctional relationship on me&hellip; I will pay the price for this mess.&quot; For  on the cross, all sin, all evil and death came upon Jesus and he paid the  price, in his blood, to put the whole world to rights i.e. to restore the world into a harmonious relationship with God that has no evil, suffering and death.<sup><a href="#fn_5">v</a></sup> However, God did not just pay the price of sin upon the cross, God also  defeated the power of sin, evil and even death,<sup><a href="#fn_6">vi</a></sup> and therefore made it possible for all things to be made new again.<sup><a href="#fn_7">vii</a></sup></p>
				<p>Jesus' death on the cross paid the price to  put the whole world to rights, but that also includes the price for our  personal sin. For we all have been in dysfunctional relationship with God. We have  turned our backs on God and have done things our own way rather than following  Him.<sup><a href="#fn_8">viii</a></sup> But on the cross, God has dealt with sin and therefore has made it possible for people to be forgiven and  reconciled into a right relationship with him. Therefore, on the cross, we  firstly see the seriousness of our sin; Jesus had to experience death in order  to deal with it.<sup><a href="#fn_9">ix</a></sup> Secondly, we see how much God loves us; He was prepared to die for us.<sup><a href="#fn_10">x</a></sup> Even if we were the only person in the world, Jesus would have died for  you&mdash;that is how much he loves you. Thirdly, we see that God is not aloof and  detached from the hurts of the world that are caused by sin, but rather he is  intimately involved; He has identified with our hurt and pain.</p>
			</div>
			<div class="cs" style="width:50px; height:608px;"><!----></div>
			<div style="width:400px; float:left;">
				<p class="mt0">Consequently, all you have to do to become  a Christian is receive God's free offer of forgiveness and enter into a right  relationship with him. You can do this by:</p>
				<ol>
					<li>Thanking God for his amazing love, that He was prepared to die       for you and make it possible to restore the relationship between you and       Him.</li>
					<li>Placing your complete trust in Jesus; believing that (1) in Him       you can find forgiveness and be restored to a right relationship with God,       and (2) that following him is the right way to live; the way of truth and       eternal life. The Bible calls this faith.</li>
					<li>Acknowledging and confessing that you have been in a broken       relationship with God; that you have lived life your own way and have not       followed God's ways. </li>
					<li>Turning from following your own desires and anything evil to       following Jesus. The Bible calls this repentance.</li>
					<li>Asking God to send the Holy Spirit into your life so that you       can be in a right relationship with Him and can become more and more like       Jesus.</li>
					<li>Starting the great adventure with God by daily following Jesus       in the power of the Holy Spirit&hellip;</li>
				</ol>
				<div id="prayerText" style="width:350px;">
					<p class="m0 f12"><strong>If you  would like to join the adventure and become a Christian, why not pray this prayer  now?</strong></p>
					<p class="mb0"><em>Dear Father God. Thank you for loving me so much  that you sent your son to die for me so that I could be forgiven and reconciled into a right  relationship with you. Please forgive me for turning my back on you; for  doubting you; for doing things my way. And forgive me for the wrong things I  have done to other people and you. And forgive me for not doing what is right  and good. Please fill me with your Holy Spirit, so that I can understand and  experience more of this love that you have for me; so that I can be in a living  relationship with you. Help me, by your Holy Spirit, to trust you more; help me  to have more faith. And help me, by your Holy Spirit, to turn away from my old  destructive ways and help me to follow Jesus each day; to become more and more  like Jesus.</em></p>
					<p class="mb0"><em>Amen </em></p>
				</div>
			</div>
			<div class="cc0"><!----></div>
			<div class="hr"><!----></div>
	
			<ol class="mb0" id="fn">
				<li> <a name="fn_1" id="fn_1"></a>See Genesis chapter 3.</li>
				<li><a name="fn_2" id="fn_2"></a> Romans 6:23 - &quot;For the  wages of sin is death...&quot;</li>
				<li> <a name="fn_3" id="fn_3"></a>John 3:16 - &quot;For God  so loved the world that he gave his one and only Son, that whoever believes in  him shall not perish but have eternal life.&quot;</li>
				<li> <a name="fn_4" id="fn_4"></a>2 Corinthians 5:18-19 - &quot;All this is from God, who reconciled us to himself through Christ and  gave us the ministry of reconciliation: that God was reconciling the world to  himself in Christ, not counting men's sins against them.&quot;</li>
				<li><a name="fn_5" id="fn_5"></a>2 Corinthians  5:21 - &quot;God made him who had no sin to be sin for us, so that  in him we might become the righteousness of God.&quot;</li>
				<li><a name="fn_6" id="fn_6"></a>See 1 Corinthians 15:55 and Colossians 2:15.</li>
				<li><a name="fn_7" id="fn_7"></a>2 Corinthians 5:17 - &quot;Therefore,  if anyone is in Christ, he is a new creation; the old has gone, the new has  come!&quot;<br /> Revelation 21:5 -  &quot;I am making everything new!&quot;</li>
				<li><a name="fn_8" id="fn_8"></a> Romans 3:23 - &quot;for all  have sinned and fall short of the glory of God&quot;</li>
				<li> <a name="fn_9" id="fn_9"></a>Mark 10:45 - &quot;The  Son of Man [Jesus] ... came to give his life as a ransom for many.&quot;</li>
				<li><a name="fn_10" id="fn_10"></a>Romans 5:8 - &quot;But God demonstrates his own love for us in this:  While we were still sinners, Christ died for us.&quot;</li>
			</ol>

			<div class="cc0"><!----></div>
			<div class="hr"><!----></div>			
			</div>


			<div class="newtab" id="tab_4" style="display:none;">
			<a name="3" id="3"></a><h2>Finding out more</h2>

		<p>If you would like to find out more about Christianity and how to become a Christian,<br /> 
		  we recommend finding a local church  that is running either an <em>Alpha Course</em> or <em>Christianity Explored</em>  course.</p>
			<img src="images/static_page_photos/Bible_study.jpg" width="366" height="253" align="right" class="photo_border" />
			<p>See the following links:</p>
			<p><a href="http://www.alpha.org/" target="_blank">www.alpha.org</a><br /> <a href="http://www.christianityexplored.org/" target="_blank">www.christianityexplored.org</a></p>
			<p>Alternatively we recommend reading one of  the following books:</p>
			<ul>
				<li>C.S. Lewis, <strong><em>Mere Christianity</em></strong>,  London: Collins/Fontana, 1953.</li>
				<li>John Stott, <strong><em>Basic Christianity</em></strong>,  Leicester: Inter-Varsity Press, 1958.</li>
				<li>Tom Wright, <strong><em>Simply Christian</em></strong>,  London: SPCK, 2006.</li>
      </ul>
		<p><br />
	      The <a href="http://www.christianity.org.uk/">Christian Enquriy Agency</a> can  provide further  information for those wanting to know more about the Christian faith and &quot;<a href="http://matthiasmedia.com.au/2wtl"  target="_blank">2 Ways to Live</a>&quot; is a helpful presentation.
			<br /><br />
		If you are looking for your local church, a helpful resource is <a href="http://www.findachurch.co.uk/">Find a Church.</a></p>
			<p><a href="#" onclick="history.go(-1);">Return to the previous page</a>        </p>
			<div class="fn_border"><!----></div>

			</div>
			
			
			
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
