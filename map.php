<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Map - EKYAM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 100vh; }
        .custom-div-icon {
            background: transparent;
            border: none;
        }
        .leaflet-popup-content {
            margin: 0;
        }
        .leaflet-popup-content-wrapper {
            padding: 0;
            border-radius: 8px;
        }
        .search-box {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
            width: 300px;
        }
        .filter-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="m-0 p-0">
    <!-- Navigation -->
    <nav class="bg-indigo-700 text-white shadow-lg fixed w-full z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="Homepage.php" class="text-2xl font-bold flex items-center">
                <span class="mr-2"><i class="fas fa-people-group"></i></span>
                EKYAM
            </a>
            <div class="flex items-center space-x-4">
                <a href="Homepage.php" class="hover:text-indigo-200">Back to Home</a>
            </div>
        </div>
    </nav>

    <!-- Map Container -->
    <div id="map"></div>

    <!-- Search Box -->
    <div class="search-box">
        <div class="relative">
            <input type="text" id="searchInput" 
                   class="w-full px-4 py-2 rounded-lg shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500" 
                   placeholder="Search communities...">
            <button class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="filter-panel">
        <h3 class="font-semibold mb-3">Filter Communities</h3>
        <div class="space-y-2">
            <label class="flex items-center">
                <input type="checkbox" class="community-filter" value="gardening" checked>
                <span class="ml-2">Gardening</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" class="community-filter" value="technology" checked>
                <span class="ml-2">Technology</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" class="community-filter" value="art" checked>
                <span class="ml-2">Art</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" class="community-filter" value="health" checked>
                <span class="ml-2">Health</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" class="community-filter" value="education" checked>
                <span class="ml-2">Education</span>
            </label>
            <label class="flex items-center">
                <input type="checkbox" class="community-filter" value="sustainability" checked>
                <span class="ml-2">Sustainability</span>
            </label>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize the map
        const map = L.map('map').setView([20.5937, 78.9629], 5);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Sample community data (replace with your actual data)
        const communities = [
            {
                name: "Urban Gardeners",
                location: [28.6139, 77.2090],
                members: 50,
                type: "gardening",
                description: "Community focused on urban gardening and sustainable living"
            },
            {
                name: "Tech Innovators",
                location: [19.0760, 72.8777],
                members: 120,
                type: "technology",
                description: "Tech enthusiasts and innovators sharing knowledge"
            },
            {
                name: "Art Collective",
                location: [12.9716, 77.5946],
                members: 75,
                type: "art",
                description: "Artists and creatives collaborating on projects"
            },
            {
                name: "Health & Wellness",
                location: [22.5726, 88.3639],
                members: 90,
                type: "health",
                description: "Promoting health and wellness in the community"
            },
            {
                name: "Education Hub",
                location: [13.0827, 80.2707],
                members: 200,
                type: "education",
                description: "Educational initiatives and learning resources"
            },
            {
                name: "Sustainability Group",
                location: [18.5204, 73.8567],
                members: 150,
                type: "sustainability",
                description: "Environmental sustainability and green initiatives"
            }
        ];

        // Store markers for filtering
        const markers = [];

        // Function to create marker
        function createMarker(community) {
            const icon = L.divIcon({
                className: 'custom-div-icon',
                html: `<div class="bg-indigo-600 text-white p-2 rounded-full shadow-lg">
                    <i class="fas fa-${getIconForType(community.type)}"></i>
                </div>`,
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            const marker = L.marker(community.location, { icon: icon })
                .bindPopup(`
                    <div class="p-4">
                        <h3 class="font-bold text-lg">${community.name}</h3>
                        <p class="text-gray-600">${community.members} members</p>
                        <p class="text-sm mt-2">${community.description}</p>
                        <div class="mt-4 flex justify-between items-center">
                            <a href="community_details.php?name=${encodeURIComponent(community.name)}" 
                               class="text-indigo-600 hover:text-indigo-800 text-sm">View Details</a>
                            <button class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700"
                                    onclick="joinCommunity('${community.name}')">Join</button>
                        </div>
                    </div>
                `);
            
            marker.communityType = community.type;
            return marker;
        }

        // Add all markers to map
        communities.forEach(community => {
            const marker = createMarker(community);
            marker.addTo(map);
            markers.push(marker);
        });

        // Function to get appropriate icon
        function getIconForType(type) {
            const icons = {
                gardening: 'leaf',
                technology: 'laptop-code',
                art: 'palette',
                health: 'heartbeat',
                education: 'graduation-cap',
                sustainability: 'recycle'
            };
            return icons[type] || 'users';
        }

        // Handle filter changes
        document.querySelectorAll('.community-filter').forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                const selectedTypes = Array.from(document.querySelectorAll('.community-filter:checked'))
                    .map(cb => cb.value);
                
                markers.forEach(marker => {
                    if (selectedTypes.includes(marker.communityType)) {
                        marker.addTo(map);
                    } else {
                        marker.remove();
                    }
                });
            });
        });

        // Handle search
        document.getElementById('searchInput').addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            markers.forEach(marker => {
                const community = communities.find(c => 
                    c.location[0] === marker.getLatLng().lat && 
                    c.location[1] === marker.getLatLng().lng
                );
                
                if (community && community.name.toLowerCase().includes(searchTerm)) {
                    marker.addTo(map);
                } else {
                    marker.remove();
                }
            });
        });

        // Handle location button
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    const { latitude, longitude } = position.coords;
                    map.setView([latitude, longitude], 13);
                    
                    const userIcon = L.divIcon({
                        className: 'custom-div-icon',
                        html: '<div class="bg-green-500 text-white p-2 rounded-full shadow-lg"><i class="fas fa-user"></i></div>',
                        iconSize: [30, 30],
                        iconAnchor: [15, 15]
                    });
                    
                    L.marker([latitude, longitude], { icon: userIcon })
                        .bindPopup('Your Location')
                        .addTo(map);
                },
                error => {
                    console.error('Error getting location:', error);
                }
            );
        }

        // Function to handle joining a community
        function joinCommunity(communityName) {
            // Replace with your actual join community logic
            alert(`Joining ${communityName}...`);
        }
    </script>
</body>
</html> 