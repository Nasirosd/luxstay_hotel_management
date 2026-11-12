<?php $pageTitle='Welcome'; require_once __DIR__.'/includes/header.php'; ?>
<section class="hero"><div class="hero-overlay"></div><div class="container hero-inner">
<span class="eyebrow light">WELCOME TO LUXSTAY</span><h1>Make room for<br><em>something wonderful.</em></h1>
<p>Thoughtful comfort, welcoming spaces and a smoother way to plan your next stay.</p>
<a class="btn btn-gold btn-lg" href="booking/rooms.php">Explore Rooms&nbsp; →</a>
</div><div class="hero-note">YOUR STAY, BEAUTIFULLY CONSIDERED</div></section>
<section class="search-wrap"><div class="container"><form class="search-panel" action="booking/rooms.php" method="get">
<div><label for="arrival">CHECK IN</label><input id="arrival" name="check_in" type="date" required></div>
<div><label for="departure">CHECK OUT</label><input id="departure" name="check_out" type="date" required></div>
<div><label for="guests">GUESTS</label><select id="guests" name="guests"><option>1</option><option selected>2</option><option>3</option><option>4</option></select></div>
<button class="btn btn-brand" type="submit">Find a Room →</button></form></div></section>
<section class="section"><div class="container"><div class="section-heading"><span class="eyebrow">A BETTER WAY TO STAY</span><h2>Comfort in every detail</h2><p>Whether you are visiting for work or taking a break, find a space that feels just right.</p></div>
<div class="row g-4"><div class="col-md-4"><article class="feature"><div class="feature-icon">✧</div><h3>Thoughtful comfort</h3><p>Carefully prepared rooms designed to make your stay easy and restful.</p></article></div>
<div class="col-md-4"><article class="feature"><div class="feature-icon">⌖</div><h3>Convenient location</h3><p>Plan your visit with useful local access and essentials close at hand.</p></article></div>
<div class="col-md-4"><article class="feature"><div class="feature-icon">♡</div><h3>Warm hospitality</h3><p>A welcoming experience supported by a simple booking process.</p></article></div></div></div></section>
<section class="section muted"><div class="container"><div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4"><div><span class="eyebrow">STAY YOUR WAY</span><h2 class="mb-0">Rooms made for you</h2></div><a class="text-link" href="booking/rooms.php">Explore all rooms →</a></div>
<div class="row g-4">
<div class="col-md-4"><article class="room-card"><img src="https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=900&q=85" alt="Classic hotel room"><div><small>CLASSIC COMFORT</small><h3>Classic Room</h3><p>A calm, inviting space for a comfortable city stay.</p><a class="text-link" href="booking/rooms.php">View room →</a></div></article></div>
<div class="col-md-4"><article class="room-card"><img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=900&q=85" alt="Deluxe hotel suite"><div><small>MORE SPACE</small><h3>Deluxe Suite</h3><p>Extra room to unwind, work and enjoy your time away.</p><a class="text-link" href="booking/rooms.php">View room →</a></div></article></div>
<div class="col-md-4"><article class="room-card"><img src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=900&q=85" alt="Premium hotel room"><div><small>A SPECIAL ESCAPE</small><h3>Premium Room</h3><p>Elevated details for a stay that feels special.</p><a class="text-link" href="booking/rooms.php">View room →</a></div></article></div></div></div></section>
<section class="cta"><div class="container"><span class="eyebrow light">YOUR NEXT CHAPTER STARTS HERE</span><h2>Ready for a more relaxing stay?</h2><p>Find your room and send your booking request in just a few steps.</p><a class="btn btn-gold btn-lg" href="booking/rooms.php">Book Your Stay →</a></div></section>
<?php require_once __DIR__.'/includes/footer.php'; ?>
