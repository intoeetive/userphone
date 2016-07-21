<h1>UserPhone for ExpressionEngine 2.x</h1>

	<p>This module allows you to request site members to enter their phones and validate them via SMS (by asking users to enter the code they've received in SMS). When phone is verified, the user can be moved to another group.</p>
    
    <p>To send verification SMS, you will need to use third-party SMS gateway service. Currently following are supported: <a href="http://www.twilio.com/">Twilio</a>, <a href="http://www.clickatell.com/">Clickatell</a><!--, <a href="http://www.textmagic.com/">TextMagic</a>--> and <a href="http://www.smsglobal.com/">SMS Global</a>.</p>

	<h3>SMS Template</h3>
	
	<p>SMS template is defined in module settings. Make sure to include <strong>{code}</strong> variable that will contain verification code.</p>
	
	<h2>Display user phone</h2>
	
	<p><strong>{exp:userphone:phone}</strong></p>
	
	<code>{exp:userphone:phone member_id="CURRENT_USER"}</code>
	
	<code>{exp:userphone:phone username="{segment_2}"}</code>
	
	<p><strong>Parameters</strong> (all optional): 
<ul>
<li><dfn>member_id</dfn> &mdash; if of member to get verified phone number. Use member_id="CURRENT_USER" to display logged in user's phone.</li>
<li><dfn>username</dfn> &mdash; alternatively, user member's username</li>
</ul>
</p>
	
	<h2>Adding phone number</h2>
	
	<p>To let user's add or modify phone number, you'll need to use 'add' tag.</p>
	<p><strong>{exp:userdata:add}</strong></p>

<code>
{exp:userphone:add return="/user/verify-phone"}<br />
&lt;p&gt;Please provide your phone number. You will receive SMS with verification code.&lt;/p&gt;<br />
&lt;p&gt;&lt;input name="phone" value="{phone}" /&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;input type="submit" value="Add phone" /&gt;&lt;/p&gt;<br />
{/exp:userphone:add}
</code> 

<p><strong>Tag parameters:</strong>
<ul>
<li><dfn>return</dfn> &mdash; a page to return to after submitting the form. Can be a full URL or URI segments.<br />Use <em>return="SAME_PAGE"</em> to return user to the page used to display this form.</li>
<li><dfn>skip_success_message="yes"</dfn> &mdash; force redirect to return page without showing success message.</li>
<li><dfn>ajax="yes"</dfn> &mdash; turn on AJAX mode. The success or error messages shown upon submission will be shown as simple text, without using message templates. The "return" parameter will not be functional if you supply this parameter.</li>
<li><dfn>id</dfn> &mdash; form ID (defaults to 'userphone_form')</li>
<li><dfn>class</dfn> &mdash; form class (defaults to 'userphone_form')</li>
<li><dfn>name</dfn> &mdash; form name (defaults to 'userphone_form')</li>
</ul>
</p>

<p><strong>Form fields</strong>:
<ul>
<li><dfn>phone</dfn> &mdash; input for phone number</li>
</ul>
</p>

<p><strong>Variables</strong>:
<ul>
<li><dfn>{phone}</dfn> &mdash; existing phone number recorded for user</li>
</ul>
</p>


	<h2>Phone verification</h2>
	
	<p>When the phone is added, the user received SMS with verification code. To get phone number verified, he has to enter in to the form created using  'verify' tag.</p>
	<p><strong>{exp:userphone:verify}</strong></p>

<code>
{exp:userphone:verify return="/user"}<br />
&lt;p&gt;<br />
Please enter the code you have received in SMS. <br />
&lt;a href="{request_new_code}"&gt;Request new code&lt;/a&gt;<br />
&lt;/p&gt;<br />
&lt;p&gt;&lt;input name="code" value="" /&gt;&lt;/p&gt;<br />
&lt;p&gt;&lt;input type="submit" value="Verify phone" /&gt;&lt;/p&gt;<br />
{/exp:userphone:verify}
</code> 

<p><strong>Tag parameters:</strong>
<ul>
<li><dfn>return</dfn> &mdash; a page to return to after submitting the form. Can be a full URL or URI segments.<br />Use <em>return="SAME_PAGE"</em> to return user to the page used to display this form.</li>
<li><dfn>skip_success_message="yes"</dfn> &mdash; force redirect to return page without showing success message.</li>
<li><dfn>ajax="yes"</dfn> &mdash; turn on AJAX mode. The success or error messages shown upon submission will be shown as simple text, without using message templates. The "return" parameter will not be functional if you supply this parameter.</li>
<li><dfn>id</dfn> &mdash; form ID (defaults to 'userphone_form')</li>
<li><dfn>class</dfn> &mdash; form class (defaults to 'userphone_form')</li>
<li><dfn>name</dfn> &mdash; form name (defaults to 'userphone_form')</li>
</ul>
</p>

<p><strong>Form fields</strong>:
<ul>
<li><dfn>code</dfn> &mdash; input for verification code from SMS</li>
</ul>
</p>

<p><strong>Variables</strong>:
<ul>
<li><dfn>{request_new_code}</dfn> &mdash; link to request new code in new SMS</li>
<li><dfn>{phone}</dfn> &mdash; pending phone number that awaits verification</li>
</ul>
</p>