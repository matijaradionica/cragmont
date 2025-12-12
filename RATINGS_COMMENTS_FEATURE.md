# Ratings & Comments Feature - In Progress

## Current Status: Database & Models Complete ✅

### Completed Components

#### 1. Database Schema (Migrated)

**ratings table:**
- Binary thumbs up/down system
- One rating per user per route
- Requires ascent to rate
- Shows who rated

**comments table:**
- Threaded comments (parent_id for replies)
- Content + optional photo
- Vote counters (upvote, downvote, helpful)
- Soft deletes
- Edit tracking

**comment_votes table:**
- Tracks user votes on comments
- Types: upvote, downvote, helpful
- One vote of each type per user per comment

**comment_reports table:**
- Report inappropriate comments
- Track review status
- Admin moderation workflow

#### 2. Models Complete

**Rating Model** (`app/Models/Rating.php`)
- Relationships: user, route
- Methods: isPositive(), isNegative()

**Comment Model** (`app/Models/Comment.php`)
- Relationships: user, route, parent, replies, votes
- Threading support with recursive loading
- Vote tracking methods
- Edit permission checking (30-minute window)
- Scopes: topLevel(), mostHelpful(), fromClimbers()

**CommentVote Model** (`app/Models/CommentVote.php`)
- Relationships: user, comment
- Tracks all vote interactions

**User Model** (Updated)
- Added: ratings(), comments() relationships

**Route Model** (Updated)
- Added: ratings(), comments() relationships
- Methods: getPositiveRatingPercentage(), getUserRating()

### Feature Specifications

#### Ratings System
- ✅ Binary: Thumbs up 👍 / Thumbs down 👎
- ✅ One rating per user per route (permanent)
- ✅ Only users who logged an ascent can rate
- ✅ Shows who rated (not anonymous)
- ✅ Display average rating percentage

#### Comments System
- ✅ Threaded comments (nested replies)
- ✅ Any authenticated user can comment
- ✅ Users can edit own comments (30-min window)
- ✅ Users can delete own comments (soft delete)
- ✅ No admin approval needed

#### Comment Interactions
- ✅ Upvote/downvote buttons
- ✅ "Helpful" button (separate from voting)
- ✅ Sort by most helpful
- ✅ Filter by climbers only (users who logged ascent)

#### Display
- ✅ Show on route detail pages
- ✅ Display rating percentage
- ✅ Show comment count
- ✅ Threaded display with replies

### Remaining Implementation

#### Next Steps:
1. **Controllers** - RatingController, CommentController
   - store() - Create rating/comment
   - destroy() - Delete comment
   - vote() - Handle upvote/downvote/helpful

2. **Policies** - Authorization rules
   - RatingPolicy - Can only rate if logged ascent
   - CommentPolicy - Edit own (within 30min), delete own

3. **Routes** - Add to web.php
   - POST /routes/{route}/rate
   - POST /routes/{route}/comments
   - PUT /comments/{comment}
   - DELETE /comments/{comment}
   - POST /comments/{comment}/vote

4. **Views** - UI Components
   - Rating widget (thumbs up/down buttons)
   - Comment form (with reply support)
   - Comment thread display (recursive)
   - Vote buttons (up/down/helpful)

5. **Integration** - Route detail page
   - Add ratings section at top
   - Add comments section below route details
   - Display statistics (X% positive, Y comments)

### Advanced Features (Phase 2)
Deferred for future implementation:
- 📸 Photo attachments in comments
- @️⃣ @username tagging and mentions
- 📧 Email notifications for replies
- 🔍 Comment search functionality
- 🚩 Report review UI for admins

### Technical Details

**Vote Counting:**
Comments store denormalized vote counts for performance:
- `upvote_count` - Total upvotes
- `downvote_count` - Total downvotes
- `helpful_count` - Total helpful marks

These are updated when votes are cast/removed.

**Threading Implementation:**
Recursive relationship with eager loading:
```php
$comments = Comment::with(['user', 'replies' => function($query) {
    $query->with('user', 'replies');
}])->topLevel()->mostHelpful()->get();
```

**Rating Requirement:**
Check if user has ascent before allowing rating:
```php
$hasAscent = $route->ascents()
    ->where('user_id', auth()->id())
    ->exists();
```

**Edit Window:**
30-minute edit window enforced in model:
```php
public function canBeEditedBy(User $user): bool
{
    return $user->id === $this->user_id
        && $this->created_at->diffInMinutes(now()) < 30;
}
```

### Database Indexes

**Optimized for:**
- Finding user's rating for a route
- Loading comments by route (sorted by helpful)
- Loading comment replies (parent_id)
- Checking vote status
- Report moderation queries

### Files Created

**Migrations:**
- `2025_12_12_110833_create_ratings_table.php`
- `2025_12_12_110854_create_comments_table.php`
- `2025_12_12_110916_create_comment_votes_table.php`
- `2025_12_12_110916_create_comment_reports_table.php`

**Models:**
- `app/Models/Rating.php`
- `app/Models/Comment.php`
- `app/Models/CommentVote.php`

**Modified:**
- `app/Models/User.php` - Added relationships
- `app/Models/Route.php` - Added relationships and helper methods

### Next Session Tasks

1. Implement RatingController with store() method
2. Implement CommentController with CRUD + vote methods
3. Create policies for authorization
4. Build rating widget view component
5. Build comment thread view component
6. Integrate into routes/show.blade.php
7. Add routes to web.php
8. Test full workflow

---

**Implementation Started:** 2025-12-12
**Phase:** Core Database & Models Complete
**Status:** Ready for Controller Implementation
