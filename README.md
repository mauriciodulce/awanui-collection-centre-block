# Awanui Collection Centre Block

A custom WordPress block that displays collection centre information from Awanui Labs. The block allows editors to select a centre from a list. The selected centre's details—address, phone, opening hours, and directions—are rendered dynamically on the front end.

---

## 🧩 Features

- Selector (dropdown) to choose a Collection Centre
- Fetches live data from the public Awanui Labs API
- ⚠️ This block renders only on the front end. The editor displays only the centre selector.
- Front-end output matches selected centre
- Graceful fallback if API is unreachable
- Fully compatible with WordPress 6.4+ and PHP 8.1+

---

## 🛠️ Build & Install

1. **Clone the repository**

```bash
git clone https://github.com/mauriciodulce/awanui-collection-centre-block.git
cd awanui-collection-centre-block
```

2. **Install dependencies**

```bash
npm install
```

3. **Build the block**

```bash
npm run build
```

Or use `npm start` to watch changes while developing.

4. **Activate the plugin**

Inside WordPress admin panel → Plugins → Activate
Or via WP-CLI:

```bash
wp plugin activate awanui-collection-centre-block
```

---

## 🧪 How to Use

1. In the block editor, insert **"Awanui Collection Centre"** block.
2. Choose a location from the dropdown list.
3. The editor saves the selected centre but does not render a preview.
4. On the front end, the full information is displayed dynamically.
---

## 🌐 API Source

This plugin uses the public API provided at:

```
https://loc.aphg.co.nz/wp-json/labtests/v1/centres/
```

Data is proxied via a local REST endpoint:
`/wp-json/awanui/v1/centres`

---

## 📦 Folder Structure

```
awanui-collection-centre-block/
├── awanui-collection-centre-block.php
├── block.json
├── src/
│   ├── index.js
│   ├── style.scss
│   ├── editor.scss
│   └── render.php
├── build/
├── assets/
├── languages/
└── README.md
```

---

## 🧹 Notes

- No third-party libraries were used (vanilla WP utilities only)
- Styling is minimal to match layout, but focus is on data rendering
- Error handling is included for API downtime

---

## 🧾 Author

**Mauricio Dulce**
- Email: [mauricio.dulce@gmail.com]
- GitHub: [https://github.com/mauriciodulce](https://github.com/mauriciodulce)

---

## 📹 Deliverables

- ✅ Plugin source code (this repo)
- ✅ Screen recording (editor + frontend demo):
  [Watch video](https://f003.backblazeb2.com/file/dropshare-uploads/Screen-Recording-2025-06-25-17-07-33.mp4)
- ✅ Setup instructions (this README)

---
