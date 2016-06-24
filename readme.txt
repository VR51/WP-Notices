=== WP Notices ===
Contributors: leehodson
Tags: notices,messages,members,membership,timed,regular,periodic
Donate link: https://paypal.me/vr51
Requires at least: 4.0.0
Tested up to: 4.5.3
Stable tag: 1.0.0
License: GPL3

Display notice messages to visitors, admin users, editors, contributors and anonymous readers. Notices can last forever, display between specific dates or at specified times of specified days regularly. Automatically convert notices to images if desired.

== Description ==
WP Notices is a flexible way to display notices to readers.

Notices can be targeted by WordPress user role (admin, editor, contributor, author, subscriber, reader, anon or any other configured role), by WordPress capabilities (manage_plugins, read, delete_pages or any other configured capability) or targeted at a specific user.

Notice messages can be displayed as text or each message can be automatically converted to a PNG image. Useful for displaying advisory notices that you prefer search engines do not index as text. Useful for creating post images on the fly. The image directory is wiped periodically so download and move images to your WP media library if you want to keep them.

The time or period of display can be set using natural language. Display notices forever (by leaving out a start and end time period), from a set start time to an open ended end date, for a specific day, range of days for one time or ad infinitum. For example start=\Thursday 8am\ End=\Friday\ would display a notice every Thursday from 8am until start of Friday or start=\Thursday 8am\ End=\Friday 4:30pm\ would display a notice every Thursday from 8am until 4:30pm Friday; you can even say start=\Wednesday 1st June 2016 8am\ End=\Friday 24th June 2016\.

The notice can be styled to change its appearance. There are four inbuilt style classes (alert-success, alert-info, alert-warning and alert-danger).

There is a help message built into WP Notices. When using the WP Notices shortcode set help=\true\ to display the help information.

WP Notices is written to be used to display notices to specific users, user groups or to all readers. It can easily also be used as a flexible lightweight way to show page content to specific users (the style classes are optional) or as a membership plugin (though you would need to configure your membership roles manually).

WP Notices has been tested. It is known to work. If you find a bug, let us know.

== Installation ==
Install as you would any other WordPress plugin.

== Frequently Asked Questions ==
=== Instructions ===
<p>The shortcode with all attributes is:</p>

<p><strong>[wp-notice to='admin' class='alert-success' start='Tuesday 1pm' end='Tuesday 5pm' image='portrait']</strong>Message to display to admin users every Tuesday between 1pm and 5pm.<strong>[/wp-notice]</strong></p>

<p>The shortcode has 6 attributes: to='', class='', start='', end='', image='' and format=''.</p>
<ul>
 	<li><strong>to=''</strong> (required) is the addressee of the notice. This can be a WordPress user role, a WordPress capability or a registered username. Usernames must be prefixed with an <strong>@</strong>. See notes below for more details.</li>
 	<li><strong>class=''</strong> (optional) but determines the design of the notice. Any custom CSS class can be used. The default CSS classes are alert-info, alert-success, alert-warning and alert-danger. These correspond to Bootstrap alerts.</li>
 	<li><strong>start=''</strong> and <strong>end=''</strong> (optional) attributes set the start date and end date for the notice to display. These attributes accept the time of day as well. These attributes accept natural language date and time expressions as well as regular year-month-day formats. You can specify times after the date such as start='2016-12-28 10pm' end='2016-12-28 11pm'. Unless a time is specified, the start date will begin at 12 midnight and the end date will end at 12 midnight e.g start='1st Jan 2016' end='2nd Jan 2016' will count as 24 hours i.e start of day on the 1st to start of day on the second. If no end date is given then the end date will always default to 'tomorrow' i.e. never expires. State no start date to show the message forever. Want the notice to display at a particular period of the day every day? Specify times without dates.</li>
 	<li><strong>image=''</strong> (optional) is used to convert the notice into an image file. The image file is then displayed instead of any text. This is useful for when you prefer to not have text in a public notice indexed by a search engine. For example, you may need to display a sponsored post awareness message above posts; instead of displaying the message as text you can choose to display it as an image. The options are image='portrait' and image='landscape'. When the image option is specified we also create a standalone PDF and HTML version of the notice; which you might find useful.</li>
 	<li><strong>format=''</strong> (optional) is used to specify the paper sized format of the images. For example, A4, B4 or C4. Adjusting this setting could improve legibility of the text within the image. The default value is C4. See notes below for more information.</li>
</ul>
<p>The message displayed can contain any HTML that is allowed within regular post content such as &lt;h1&gt;, &lt;p&gt;, &lt;em&gt;, &lt;strong&gt;, &lt;br&gt;, &lt;hr&gt;, &lt;ul&gt; and so on. If you use HTML tags that do not show in the message then that will be because they were filtered out.</p>

<p>As well as regular WordPress user roles and capabilities, there are several aliases you can use in the <strong>to=''</strong> attribute. For example to='admin' is the same as to='administrator', use 'anon' to reach all none logged in users, use 'loggedin' to reach all loggedin users and use 'everyone' to display your message to everyone.</p>

<p>User roles, capabilities and usernames cannot currently be combined to reach multiple groups or multiple users.</p>

<p>User roles are role specific e.g. to='admin' will display a message to admin users but not to editors, authors, contributors or subscribers.</p>

<p>Notices that target user capabilities cascade upwards to users with higher capabilities but not downwards to those with lower capabilities e.g. if the notice is to='delete_others_pages' then editors and admins will see the message (they both share this capability) but authors, contributors and subscribers will not see the message.</p>

<h2>Image formats for format='' are:</h2>
<ul>
 	<li>4a0 (4767.87, 6740.79),<i></i></li>
 	<li>2a0 (3370.39, 4767.87),<i></i></li>
 	<li>a0 (2383.94, 3370.39),<i></i></li>
 	<li>a1 (1683.78, 2383.94),<i></i></li>
 	<li>a2 (1190.55, 1683.78),<i></i></li>
 	<li>a3 (841.89, 1190.55),<i></i></li>
 	<li>a4 (595.28, 841.89),<i></i></li>
 	<li>a5 (419.53, 595.28),<i></i></li>
 	<li>a6 (297.64, 419.53),<i></i></li>
 	<li>a7 (209.76, 297.64),<i></i></li>
 	<li>a8 (147.40, 209.76),<i></i></li>
 	<li>a9 (104.88, 147.40),<i></i></li>
 	<li>a10 (73.70, 104.88),<i></i></li>
 	<li>b0 (2834.65, 4008.19),<i></i></li>
 	<li>b1 (2004.09, 2834.65),<i></i></li>
 	<li>b2 (1417.32, 2004.09),<i></i></li>
 	<li>b3 (1000.63, 1417.32),<i></i></li>
 	<li>b4 (708.66, 1000.63),<i></i></li>
 	<li>b5 (498.90, 708.66),<i></i></li>
 	<li>b6 (354.33, 498.90),<i></i></li>
 	<li>b7 (249.45, 354.33),<i></i></li>
 	<li>b8 (175.75, 249.45),<i></i></li>
 	<li>b9 (124.72, 175.75),<i></i></li>
 	<li>b10 (87.87, 124.72),<i></i></li>
 	<li>c0 (2599.37, 3676.54),<i></i></li>
 	<li>c1 (1836.85, 2599.37),<i></i></li>
 	<li>c2 (1298.27, 1836.85),<i></i></li>
 	<li>c3 (918.43, 1298.27),<i></i></li>
 	<li>c4 (649.13, 918.43),<i></i></li>
 	<li>c5 (459.21, 649.13),<i></i></li>
 	<li>c6 (323.15, 459.21),<i></i></li>
 	<li>c7 (229.61, 323.15),<i></i></li>
 	<li>c8 (161.57, 229.61),<i></i></li>
 	<li>c9 (113.39, 161.57),<i></i></li>
 	<li>c10 (79.37, 113.39),<i></i></li>
 	<li>ra0 (2437.80, 3458.27),<i></i></li>
 	<li>ra1 (1729.13, 2437.80),<i></i></li>
 	<li>ra2 (1218.90, 1729.13),<i></i></li>
 	<li>ra3 (864.57, 1218.90),<i></i></li>
 	<li>ra4 (609.45, 864.57),<i></i></li>
 	<li>sra0 (2551.18, 3628.35),<i></i></li>
 	<li>sra1 (1814.17, 2551.18),<i></i></li>
 	<li>sra2 (1275.59, 1814.17),<i></i></li>
 	<li>sra3 (907.09, 1275.59),<i></i></li>
 	<li>sra4 (637.80, 907.09),<i></i></li>
 	<li>letter (612.00, 792.00),<i></i></li>
 	<li>legal (612.00, 1008.00),<i></i></li>
 	<li>ledger (1224.00, 792.00),<i></i></li>
 	<li>tabloid (792.00, 1224.00),<i></i></li>
 	<li>executive (521.86, 756.00),<i></i></li>
 	<li>folio (612.00, 936.00),<i></i></li>
 	<li>commercial #10 envelope (684, 297),<i></i></li>
 	<li>catalog #10 1/2 envelope (648, 864),<i></i></li>
 	<li>8.5x11 (612.00, 792.00),<i></i></li>
 	<li>8.5x14 (612.00, 1008.0),<i></i></li>
 	<li>11x17 (792.00, 1224.00),<i> </i></li>
</ul>
<h2>Reference Links</h2>
<p><a href="http://php.net/manual/en/datetime.formats.relative.php" target="_blank">PHP natural language time reference information (Relative Times)</a>.</p>

<p><a href="https://codex.wordpress.org/Roles_and_Capabilities" target="_blank">WordPress roles and capabilities list</a>. These are used in the "to" field.</p>

== Screenshots ==
1. Info Message

== Changelog ==
1.0.0

23rd June 2016

- First public release
