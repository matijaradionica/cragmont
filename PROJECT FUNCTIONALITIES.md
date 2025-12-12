# Climbing Route Database - Feature Specification

## I. Database and Detailed Entry (The Core)

### Route Creation
A form for detailed entry of all essential route data.

#### Identification
- Name
- Location (mountain, cliff, sector)
- GPS coordinates

#### Technical Specifications
- Length (meters)
- Pitch count
- Difficulty grade (e.g., UIAA/French)

#### Risk and Type
- Danger Rating (R/X) tag
- Route type (Alpine, Sport, Traditional)

#### Logistics
- Approach description
- Descent description
- Required gear (rack)

### Topo and Visuals
- Ability to upload Topo diagram images (externally created)
- Additional cliff photographs

### Status Management
Defining the route's current status:
- New
- Equipped
- Needs Repair
- Closed

---

## II.  Search and Geolocation

### Advanced Search
Combined filters for searching by:
- Name
- Location
- Grade range
- Pitch count
- Risk rating (R/X)

### Interactive Map
- Display of all route locations on an interactive map (Google Maps/Leaflet)
- Markers with quick information popups

### Mobile Optimization
- Fast and legible display on mobile devices
- Crucial for field use

---

## III. User Profile and Community

### Authentication
- Standard user registration/login
- Implementation using Laravel Breeze/Jetstream

### Logbook (Ascent Journal)
Every user can record an ascent of a route:
- Date
- Partners
- Success/Failure
- Personal impression

### Ratings and Comments
- **Quality Rating:** Rate climbing quality and gear (1-5 stars)
- **Condition Reports:** Comments for reporting current conditions (snow, flooded areas, poor anchors)

### Lists
Creation of personal route lists:
- "Climbed"
- "Wishlist"

---

## IV. Administration and Moderation

### Role System
Defining user roles:
- Admin
- Moderator
- Standard User
- Club/Equipper

### Content Moderation
- Administrative control panel
- Review and approve (or reject) all new route submissions
- Review proposed changes by standard users
- Ensures database quality

### File Management
- Easy management of uploaded Topo diagrams
- Photograph organization and storage