## LearnPress – Add Course via URL

This plugin adds a WooCommerce-style "Add to Cart" functionality to LearnPress via a simple URL. It allows you to create direct links that add a specific course to the cart and optionally redirect the user straight to the checkout page.

The plugin:

- **Adds a "Checkout URL" metabox** to the LearnPress Course editor (sidebar).
- **Generates a unique URL** for each course that automatically adds it to the cart.
- **Includes a "Copy URL" button** in the admin panel for quick access.
- **Supports clean redirects** to the checkout page after adding the course.

---

## Requirements

- **WordPress**: 6.3 or higher  
- **PHP**: 7.4 or higher  
- **LearnPress**: 4.0.0 or higher  

---

## How to Use

Once the plugin is activated:

1. Edit any **LearnPress Course**.
2. Look for the **Checkout URL** metabox in the side panel.
3. You will see a ready-to-use link.
4. Click **Copy URL** (Копіювати URL) to copy it to your clipboard.

### Manual URL Construction
Alternatively, you can manually construct the URL by adding these parameters to your checkout page URL:

- `add-course=[COURSE_ID]` — The ID of the course you want to add.
- `redirect=checkout` — (Optional) Telling the plugin to perform a clean redirect to the checkout page after adding the course.

**Example:**
`https://your-site.com/checkout/?add-course=123&redirect=checkout`

This is useful for:
- Landing pages (buttons).
- Email marketing campaigns.
- Custom price tables or external sales pages.
