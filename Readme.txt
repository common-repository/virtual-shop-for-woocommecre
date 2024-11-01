=== Virtual Shop For Woocommecre ===
Contributors: eewann
Donate link: https://thevrshop.000webhostapp.com/donate
Tags: VR, Virtual Reality, Woocommerce
Requires at least: 4.0
Tested up to: 4.8
Stable tag: trunk
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin will connect woocommerce with vrshop. providing product information and cart processing. Payment is made on the website.

== Description ==

https://www.youtube.com/watch?v=KYp-93PWlAM

This plugin allows your woocommerce to turn into a virtual store

Register free virtual store on [TheVRShop](https://thevrshop.000webhostapp.com/virtual-shop-admin "Free registration") then connect it with your woocommerce.

The same client apps are used for all stores and it's free. Currently supported platform is Window and android. The display can be switched between VR and Non-VR. Scan QRcode or enter world code to start shopping. Can be run offline.

Editor is also provided. Make sure you are online when using it.

Register and open shop for free
[Register](https://thevrshop.000webhostapp.com/virtual-shop-admin "Sign up for free")

Client Apps and editor
[Download](https://thevrshop.000webhostapp.com/download "Download client and editor")

What does this plugin do?
1. Prepare and send session key requested by main server for use by apps.
1. Provide product information from woocommerce and send it to apps. Session_key is required.
1. Accept and save the cart data sent by apps to the database then send the result along with the link for the next process. session_key required.
1. When user opens a link provided by (3), the key will be stored in the cookie and cart details will be displayed. If user is signing in, the cart will be considered their own.
1. When user clicks "Confirm", the product will be added to the woocommerce cart. Users can then make checkouts and payments as usual.

More and documentation, How to use editor, apps and so on.
[Documentation](https://thevrshop.000webhostapp.com/documentation "Getting Started")

Sample store. [I know it's not very interesting. I'm not a designer.]
[Sample](http://o-shop.esy.es/vr-shop/ "Sample Shop")

These apps are still in early stage of development and may not stable. So there might be a ton of bugs. Please report it to me. I probably not reply (because I'm not confident with my english). For any issues with apps, please use contact form at [TheVRStore](https://thevrshop.000webhostapp.com/ "Main Page").

As you can see, the main server is only using a free webhost. If you fail to connect, it may be because the server is down. Please wait a few minutes or a few hours.

This app still does not fully support VR. Because I do not have any VR set. Only tried with D.I.Y vr only.
Spec: HP Workstation, Core2 Quad processor with 12GB of RAM. nvidia GT640.

There is no premium package at this time. Donators will get some premium features when it is available.

Sorry if my english is hard to understand.

== Installation ==

1. Upload the Virtual Shop For Woocommecre plugin to your blog
1. Activate it.
1. Create a page. Then add shortcode "[wc_vr_shop]"
1. Open the page as admin. Then click "Copy Template To Current Theme".
1. Set your "Shop Key". Get it at [Virtual Shop Admin](https://thevrshop.000webhostapp.com/virtual-shop-admin "Virtual Shop Admin"). and then update.
1. Edit the page again. Change the template to "VRShop - Page Template"
1. Lastly, Copy your store configuration to your store admin at [Virtual Shop Admin](https://thevrshop.000webhostapp.com/virtual-shop-admin "Virtual Shop Admin").

== Frequently Asked Questions ==

== Screenshots ==

1. Editor
2. Client Apps
3. Plugin

== Changelog ==

= 0.1 =
* Initial Release.

== Upgrade Notice ==

