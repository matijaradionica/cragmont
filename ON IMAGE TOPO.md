# On-Image Topo Creation Feature Concept

This feature allows users to upload a high-resolution photo of a cliff face (crag) and draw the line of the climbing route directly on the image, creating a modern, user-generated **topo**.

## Core Functionality (MVP)

| Component | Description | Technologies / Implementation Note |
|---|---|---|
| **Image Upload & Display** | Standard form for uploading the crag photo. The uploaded image is displayed in a dedicated, resizable canvas area. | Laravel Livewire (reactive component), TALL stack UI. |
| **Interactive Drawing Canvas** | A JavaScript library overlays a canvas on the image, allowing users to draw vector paths. | Konva.js, Fabric.js, or Paper.js are suitable candidates for complex vector drawing. |
| **Drawing Tools** | - **Path Tool:** Free-form or segmented line drawing to trace the route. <br>- **Color/Style Picker:** Select colors (e.g., blue for Pitch 1, red for Pitch 2) and line thickness. <br>- **Eraser/Undo:** Basic correction functionality. | Implemented via the chosen JS library. Alpine.js can manage tool state. |
| **Point / Pitch Marker** | Tool to drop numbered markers/pins on the image to denote pitch starts/ends, belay stations, or cruxes. | Drawing library shapes (circles, pins) with associated metadata. |
| **Data Capture** | For the drawn line, the user must define: <br>- **Route Grade:** UIAA, French, or YDS standard (dropdown/input). <br>- **Route Name** and **Description**. | Standard form fields, linked to the drawing session. |
| **Data Storage** | Store the original image. Store the drawing path (vector data) and point markers as JSON strings with coordinates relative to the image size. | Laravel Database (e.g., `topo_data` column in the `routes` table). JSON keeps the format flexible. |


## Community & Detail Enhancements

| Component | Description | Implementation Note |
|---|---|---|
| **Annotated Markers (Tooltips)** | When placing a point marker (e.g., belay, crux), the user can add a text note. When viewing the topo, hovering over the marker shows the text (e.g., “Crux: Dyno move here,” “Bolt needs replacement”). | Alpine.js tooltips triggered by hovering over the drawing library’s shape element. |
| **Safety Ratings Overlay** | Use a specific color (e.g., dark red) or text label overlay on the drawing path to mark R/X danger sections. | Dedicated tool state that changes the color and saves a separate path segment data. |
| **Pitch Segmentation** | Allow drawing the route in multiple, color-coded segments that correspond to the pitch count. | Each segment of the drawing path is stored with an associated **Pitch Number** and **Grade**. |
