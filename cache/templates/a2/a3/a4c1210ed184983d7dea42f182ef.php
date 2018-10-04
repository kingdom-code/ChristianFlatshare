<?php

/* emails/palup.html.twig */
class __TwigTemplate_a2a3a4c1210ed184983d7dea42f182ef extends Twig_Template
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
    
    <p>A new Wanted Accommodation advert has been placed on Christian Flatshare which we think may be of interest to you:</p>
    <p><strong><a href=\"";
        // line 7
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["wanted"]) ? $context["wanted"] : null), "url"), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["wanted"]) ? $context["wanted"] : null), "title"), "html", null, true);
        echo "</a></strong></p>
    <p>This Pal-Up email has been sent in response to your Wanted Accommodation advert:</p>
    <p><strong><a href=\"";
        // line 9
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["my_wanted"]) ? $context["my_wanted"] : null), "url"), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute((isset($context["my_wanted"]) ? $context["my_wanted"] : null), "title"), "html", null, true);
        echo "</a></strong></p>
    
    <p>Use the links below to remove your advert or to indicate it is still needed:</p>
    
    <ul>
        <li><a href=\"";
        // line 14
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "\">My advert is still needed</a> (sets your &quot;last logged in&quot; date to today)</li>
        <li><a href=\"";
        // line 15
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "\">Please remove my advert</a> (suspends your advert so it is no longer shown, stops Pal-Up emails)</li>
    </ul>
    
    <p>If the above links do not work in your browser, please cut and paste these into your browser:</p>
    <p>My advert is still needed: ";
        // line 19
        echo twig_escape_filter($this->env, (isset($context["keep_url"]) ? $context["keep_url"] : null), "html", null, true);
        echo "<br />Please remove my advert: ";
        echo twig_escape_filter($this->env, (isset($context["suspend_url"]) ? $context["suspend_url"] : null), "html", null, true);
        echo "</p>
    
    <p>To disable Flat-Match emails, go to the \"Your ads\" page and edit your advert to uncheck the Flat-Match option.</p>
    
    <p class=\"title\"><strong>Not getting responses to your advert?</strong></p>
    <p>To help get the best response from your advert ensure you have provided <strong>detailed description</strong> and have <strong>added photos</strong>, which  can be fun!</p>
    
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
        return "emails/palup.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  66 => 19,  59 => 15,  55 => 14,  45 => 9,  38 => 7,  31 => 4,  28 => 3,);
    }
}
