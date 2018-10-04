<?php

/* emails/published.html.twig */
class __TwigTemplate_fd1fd6416468fc8e1c2e3ed31c0dbaa2 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("emails/base.html.twig");

        $this->blocks = array(
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "emails/base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_body($context, array $blocks = array())
    {
        // line 4
        echo "    <p><strong>Hi ";
        echo twig_escape_filter($this->env, (isset($context["first_name"]) ? $context["first_name"] : null), "html", null, true);
        echo ",</strong></p>
    <p>Your advert has been published on Christian Flatshare:</p>
    <p>Advert title: <strong>";
        // line 6
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "title"), "html", null, true);
        echo "</strong><br />Advert link: <strong><a href=\"";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "url"), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "url"), "html", null, true);
        echo "</a></strong></p>
    <p>You will be notified when someone replies to your advert, any messages to you can be seen in your account.</p>
    <p>Your advert will expire on <strong>";
        // line 8
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["ad"]) ? $context["ad"] : null), "expiry_date"), "html", null, true);
        echo "</strong>, 10 days after its &quot;";
        echo twig_escape_filter($this->env, (isset($context["date_type"]) ? $context["date_type"] : null), "html", null, true);
        echo "&quot; date. We will send you a reminder email before your advert expires.</p>
    <p>To keep your advert published for longer simply click on &quot;Your ads&quot; (top right corner) and change your advert's &quot;";
        // line 9
        echo twig_escape_filter($this->env, (isset($context["date_type"]) ? $context["date_type"] : null), "html", null, true);
        echo "&quot; date.</p>
    <p>Use the links below at anytime to remove your ad or to indicate it is needed:</p>
    
    <ul>
        <li><a href=\"";
        // line 13
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "\">My advert is still needed</a> (sets your &quot;last logged in&quot; date to today)</li>
        <li><a href=\"";
        // line 14
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "\">Please remove my advert</a> (suspends your advert so it is no longer shown, stops Flat-Match emails)</li>
    </ul>
    
    <p>If the above links do not work in your browser, please cut and paste these into your browser:</p>
    <p>";
        // line 18
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "<br />";
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "</p>
    
    <div class=\"tips\">
        <p class=\"title\"><strong>Advert Tips</strong></p>
        <p><strong>Photos</strong> - Adding photos to adverts helps greatly to get the best advert response. You can upload photos by going to &quot;Yours ads&quot; (top right corner), and by click on &quot;add photos&quot;</p>
        <p><strong>Accommodation and Household details</strong> - Taking care to give friendly and detailed descriptions of the accommodation and the household will be helpful for those reading your ad... and remember it is ok to say what may be obvious to you to those who've never seen your place or who don't know you. Edit your ad by going to &quot;Your ads&quot; and clicking on &quot;edit this ad&quot;.</p>
        <p><strong>Available From or Required From Date</strong> - Adverts automatically expire and are no longer visible on CFS 10 days after their &quot;Available From&quot; date (or &quot;Required From&quot; for Wanted ads). Before your advert expires we'll send you a message to ask if your ad is still needed.</p>
        <p>To keep your advert active on the site, log in and go to &quot;Your ads&quot; to move the &quot;Available From&quot; date forwards - this will help indicate to CFS visitors that the accommodation is still available (and not an old advert that has not been removed). Your advert's age (displayed in days on your advert) will not change, so those looking for new adverts won't be misled.</p>
    </div>
    
    <p class=\"title\"><strong>Being safe online...</strong></p>
    <p>Below are some common sense pointers to observe when interacting with others online, which may help you to spot unusual behaviour.</p>
    
    <ul>
        <li>Any requests for payments through untraceable money transfer services such as &quot;Western Union Money Transfer&quot; or &quot;Moneygram&quot; should be treated as highly suspicious. </li>
        <li><a href=\"http://www.consumerdirect.gov.uk/watch_out/scams/cheque-overpayment/\">Overpayments scams</a>: overpayments made with a request for the 'change' (sometimes blamed on a clerical error) should be treated suspiciously.</li>
        <li>Be wary of anyone who appears to want to remain distant from you, and does not wish to see you or the accommodation before parting with  money.</li>
        <li>Do not disclose your banking information to individuals over the internet.</li>
        <li>Do not send images or copies of identification, such as your driving license or passport, which can be used for identity theft.</li>
        <li>Scammers often refrain from telephone contact as they are often outside the UK.</li>
        <li>Do not sign a contract or make a payment without seeing the accommodation first. Protracted or seemingly awkward situations which make it difficult to see accommodation before paying a deposit or rent should be treated very cautiously.</li>
    </ul>
    
    <p>The <a href=\"http://www.oft.gov.uk/oft_at_work/consumer_initiatives/scams/\">Office of Fair Trading</a> and the <a href=\"http://www.dti.gov.uk/sectors/infosec/infosecadvice/general/fraudsandscams/page33294.html\">DTI</a> provide some helpful information on these matters.</p>
    <p>If your advert has ticked &quot;<strong><em>would prefer someone who if asked could provide a recommendation from a church</em></strong>&quot;, remember that you can always ask for such a church contact (website and email addresses) who can say that the responding  is known to them. This is quite in the spirit of using CFS and you should expect such points of contact within churches to be offered willingly. If you do contact that church, those working in the church's office a very likely to be all too glad to help confirm that the person responding is known to them.</p>
";
    }

    public function getTemplateName()
    {
        return "emails/published.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  70 => 18,  63 => 14,  59 => 13,  52 => 9,  46 => 8,  37 => 6,  31 => 4,  28 => 3,);
    }
}
