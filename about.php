<?php
require_once ("includes/klassica.php");

$header["title"] = _HTML_TITLE_PREFIX." - About";
$header["bodyid"] = "about";
$header['show_sideboxes'] = true;
$header["css"] = array('main', 'categories');
require_once ("includes/header.php");

?>

        <p class="titleLabel">Mission Statement</p>
          <p>Klassica is a system that empowers a community to buy, sell, and trade items and post announcements by providing a quick, easy, and open source web application.</p>
          
        <p class="titleLabel">Goals</p>
          <p>Klassica is oriented towards academic communities and student bodies, but it is free for anyone to use for the purpose of light sales transactions. Klassica also hopes to provide an effective way of sharing announcements, events, and requests.</p>

        <p class="titleLabel">Safe Buying and Selling</p>
          <p>
          Klassica's developers are not liable for any use of this product, or any of the sales which are conducted through Klassica. We are not involved in sales of items, and cannot in anyway safeguard your transactions, sales, and purchases. We realize that there is potential for fraud in online commerce, and we must warn users to use such a system at their own risk, and to be cautious about your transactions. If you are a victim of fraud, please contact the administrators of your local Klassica website, and contact your bank, or payment service (e.g. Paypal), or even law enforcement if necessary.
          
          Please use common sense when buying and selling. We encourage you do make your transaction in person. A good guide to preventing spam and fraud can be found at <a href="http://www.craigslist.org/about/scams.html">http://www.craigslist.org/about/scams.html</a> There are also many other guides to avoiding scams and spam on other websites. This warning is about all that Klassica's developers can do to help you. Unfortunately, we have very limited resources, and we do not have any control over use and content of this installation of Klassica. We simply produce an e-commerce web application that others use to build a classifieds website. Klassica's developers do not host content, attempt to censor, or even have access to the installation and user accounts that you or your organization are using. We are thus, in no way liable for misuse of this web application.</p>
          
        <p class="titleLabel">License</p>
          <p>Klassica is an open source web application granted for use under the GNU General Public License  This product and its developers make no claim or guarantee of its reliability, security, or use.</p>
          
          <p>Enjoy using this open source classified system, as it is free to use and to modify according to the GPL. For technical support, please contact <a href="../contact/">Klassica's Administrators.</a></p>

        <p class="titleLabel">Usage Disclaimer</p>
          <p>Klassica is an open source project which is free for use, but includes no commercial support. Its authors and administrators grant no warranty or guarantee whatsoever. Klassica's authors and the local site administrators have done their best to provide a secure product to their users, but can grant no warranty, or hold any liability concerning the use of this site, or any site using this software, or concerning damages or losses resulting from usage therein. Sales, trade, and auction transactions are conducted purely at the users own risk. Buyers and sellers assume full responsibility for their actions and obligations, and financial debts. Klassica's authors are to hold no liability whatsoever for any use, action, or loss that may be associated with using this product.</p>

<?php include("includes/footer.php"); ?>
