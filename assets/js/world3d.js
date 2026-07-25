(function () {
  const container = document.getElementById('world-canvas');
  const stations = window.WORLD_STATIONS || [];
  if (!container || typeof THREE === 'undefined' || !stations.length) return;

  const startOverlay = document.getElementById('world-start');
  const infoPanel = document.getElementById('world-info');
  const infoLabel = document.getElementById('world-info-label');
  const infoSummary = document.getElementById('world-info-summary');
  const infoLink = document.getElementById('world-info-link');
  const joystick = document.getElementById('world-joystick');
  const joystickNub = document.getElementById('world-joystick-nub');

  const WORLD_RADIUS = 20;
  const STATION_RING = 15;
  const PROXIMITY = 4.5;
  const SPEED = 9;

  // ---------- scene setup ----------
  const scene = new THREE.Scene();
  scene.background = new THREE.Color(0x05070a);
  scene.fog = new THREE.Fog(0x05070a, 22, 42);

  const camera = new THREE.PerspectiveCamera(55, 1, 0.1, 200);

  const renderer = new THREE.WebGLRenderer({ antialias: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
  container.appendChild(renderer.domElement);

  function resize() {
    const w = container.clientWidth;
    const h = container.clientHeight;
    if (!w || !h) return;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    renderer.setSize(w, h);
  }
  window.addEventListener('resize', resize);

  // ---------- lighting ----------
  scene.add(new THREE.HemisphereLight(0x8b9bff, 0x0a0a12, 0.9));
  const dirLight = new THREE.DirectionalLight(0xffffff, 0.6);
  dirLight.position.set(8, 14, 6);
  scene.add(dirLight);

  // ---------- ground ----------
  const ground = new THREE.Mesh(
    new THREE.CircleGeometry(WORLD_RADIUS + 4, 48),
    new THREE.MeshStandardMaterial({ color: 0x0d1117, roughness: 0.95 })
  );
  ground.rotation.x = -Math.PI / 2;
  scene.add(ground);

  const grid = new THREE.GridHelper(2 * (WORLD_RADIUS + 4), 44, 0x22d3ee, 0x1b2130);
  grid.material.transparent = true;
  grid.material.opacity = 0.35;
  scene.add(grid);

  // ---------- player ----------
  const player = new THREE.Group();
  const body = new THREE.Mesh(
    new THREE.CapsuleGeometry(0.5, 0.9, 4, 8),
    new THREE.MeshStandardMaterial({ color: 0x22d3ee, emissive: 0x0e5a66, roughness: 0.4 })
  );
  body.position.y = 0.95;
  const visor = new THREE.Mesh(
    new THREE.SphereGeometry(0.28, 16, 16),
    new THREE.MeshStandardMaterial({ color: 0xffffff, emissive: 0x333333 })
  );
  visor.position.y = 1.55;
  player.add(body, visor);
  scene.add(player);

  const playerLight = new THREE.PointLight(0x22d3ee, 1.2, 6);
  playerLight.position.set(0, 2, 0);
  player.add(playerLight);

  // ---------- stations ----------
  function makeLabelSprite(text, color) {
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 64;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = 'rgba(5,7,10,0.75)';
    ctx.roundRect ? ctx.roundRect(0, 8, 256, 48, 12) : ctx.rect(0, 8, 256, 48);
    ctx.fill();
    ctx.font = 'bold 30px Inter, sans-serif';
    ctx.fillStyle = color;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, 128, 34);
    const texture = new THREE.CanvasTexture(canvas);
    const sprite = new THREE.Sprite(new THREE.SpriteMaterial({ map: texture, transparent: true }));
    sprite.scale.set(3.4, 0.85, 1);
    return sprite;
  }

  const stationMeshes = stations.map((station, i) => {
    const angle = (i / stations.length) * Math.PI * 2;
    const x = Math.cos(angle) * STATION_RING;
    const z = Math.sin(angle) * STATION_RING;
    const color = new THREE.Color(station.color || '#22D3EE');

    const group = new THREE.Group();
    group.position.set(x, 0, z);

    const pedestal = new THREE.Mesh(
      new THREE.CylinderGeometry(1.1, 1.3, 0.6, 24),
      new THREE.MeshStandardMaterial({ color: 0x11151c, emissive: color, emissiveIntensity: 0.25, roughness: 0.6 })
    );
    pedestal.position.y = 0.3;
    group.add(pedestal);

    const gem = new THREE.Mesh(
      new THREE.IcosahedronGeometry(0.6, 0),
      new THREE.MeshStandardMaterial({ color, emissive: color, emissiveIntensity: 0.7, roughness: 0.2, metalness: 0.3 })
    );
    gem.position.y = 2;
    group.add(gem);

    const ring = new THREE.Mesh(
      new THREE.RingGeometry(1.5, 1.65, 32),
      new THREE.MeshBasicMaterial({ color, transparent: true, opacity: 0.5, side: THREE.DoubleSide })
    );
    ring.rotation.x = -Math.PI / 2;
    ring.position.y = 0.02;
    group.add(ring);

    const label = makeLabelSprite(station.label || '', station.color || '#22D3EE');
    label.position.y = 3.1;
    group.add(label);

    scene.add(group);
    return { station, group, gem, ring, phase: Math.random() * Math.PI * 2 };
  });

  // ---------- controls: keyboard ----------
  const keys = { forward: false, back: false, left: false, right: false };

  function setKey(code, val) {
    switch (code) {
      case 'KeyW':
      case 'ArrowUp':
        keys.forward = val;
        return true;
      case 'KeyS':
      case 'ArrowDown':
        keys.back = val;
        return true;
      case 'KeyA':
      case 'ArrowLeft':
        keys.left = val;
        return true;
      case 'KeyD':
      case 'ArrowRight':
        keys.right = val;
        return true;
      default:
        return false;
    }
  }

  container.addEventListener('keydown', (e) => {
    if (setKey(e.code, true)) e.preventDefault();
  });
  container.addEventListener('keyup', (e) => {
    if (setKey(e.code, false)) e.preventDefault();
  });

  // ---------- controls: virtual joystick (touch) ----------
  const joystickInput = { x: 0, y: 0 };
  let joystickTouchId = null;

  if (joystick && joystickNub) {
    const radius = 36;

    const move = (clientX, clientY) => {
      const rect = joystick.getBoundingClientRect();
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      let dx = clientX - cx;
      let dy = clientY - cy;
      const dist = Math.min(Math.hypot(dx, dy), radius);
      const angle = Math.atan2(dy, dx);
      dx = Math.cos(angle) * dist;
      dy = Math.sin(angle) * dist;
      joystickNub.style.left = `${28 + dx}px`;
      joystickNub.style.top = `${28 + dy}px`;
      joystickInput.x = dx / radius;
      joystickInput.y = dy / radius;
    };

    const reset = () => {
      joystickTouchId = null;
      joystickInput.x = 0;
      joystickInput.y = 0;
      joystickNub.style.left = '28px';
      joystickNub.style.top = '28px';
    };

    joystick.addEventListener('touchstart', (e) => {
      const t = e.changedTouches[0];
      joystickTouchId = t.identifier;
      move(t.clientX, t.clientY);
      e.preventDefault();
    });
    joystick.addEventListener('touchmove', (e) => {
      for (const t of e.changedTouches) {
        if (t.identifier === joystickTouchId) move(t.clientX, t.clientY);
      }
      e.preventDefault();
    });
    joystick.addEventListener('touchend', reset);
    joystick.addEventListener('touchcancel', reset);
  }

  // ---------- start overlay ----------
  if (startOverlay) {
    startOverlay.addEventListener('click', () => {
      startOverlay.classList.add('hidden');
      container.focus();
    });
  }

  // ---------- info panel ----------
  let activeStation = null;

  function setActiveStation(entry) {
    if (activeStation === entry) return;
    activeStation = entry;
    if (!infoPanel) return;
    if (!entry) {
      infoPanel.classList.add('hidden');
      return;
    }
    infoLabel.textContent = entry.station.label || '';
    infoLabel.style.color = entry.station.color || '#22D3EE';
    infoSummary.textContent = entry.station.summary || '';
    infoLink.href = entry.station.link || '#';
    infoPanel.classList.remove('hidden');
  }

  // ---------- camera follow rig ----------
  const cameraOffset = new THREE.Vector3(0, 5.5, 8);
  let facing = 0;

  // ---------- animation loop ----------
  const clock = new THREE.Clock();

  function animate() {
    requestAnimationFrame(animate);
    const dt = Math.min(clock.getDelta(), 0.05);
    const t = clock.elapsedTime;

    // movement input (keyboard axis takes priority, falls back to joystick)
    let ix = (keys.right ? 1 : 0) - (keys.left ? 1 : 0);
    let iz = (keys.back ? 1 : 0) - (keys.forward ? 1 : 0);
    if (ix === 0 && iz === 0) {
      ix = joystickInput.x;
      iz = joystickInput.y;
    }

    const inputLen = Math.hypot(ix, iz);
    if (inputLen > 0.05) {
      const nx = ix / (inputLen > 1 ? inputLen : 1);
      const nz = iz / (inputLen > 1 ? inputLen : 1);

      player.position.x += nx * SPEED * dt;
      player.position.z += nz * SPEED * dt;

      const dist = Math.hypot(player.position.x, player.position.z);
      if (dist > WORLD_RADIUS) {
        const scale = WORLD_RADIUS / dist;
        player.position.x *= scale;
        player.position.z *= scale;
      }

      const targetFacing = Math.atan2(nx, nz);
      let diff = targetFacing - facing;
      diff = Math.atan2(Math.sin(diff), Math.cos(diff));
      facing += diff * Math.min(dt * 10, 1);
      player.rotation.y = facing;
    }

    // camera follow (third-person, orbiting behind facing direction)
    const rotatedOffset = cameraOffset.clone().applyAxisAngle(new THREE.Vector3(0, 1, 0), facing);
    const desiredCamPos = player.position.clone().add(rotatedOffset);
    camera.position.lerp(desiredCamPos, 1 - Math.pow(0.001, dt));
    camera.lookAt(player.position.x, player.position.y + 1.2, player.position.z);

    // station bob/spin + proximity check
    let nearest = null;
    let nearestDist = Infinity;
    stationMeshes.forEach((entry) => {
      entry.gem.position.y = 2 + Math.sin(t * 1.4 + entry.phase) * 0.25;
      entry.gem.rotation.y += dt * 0.8;
      entry.ring.rotation.z += dt * 0.3;

      const dx = player.position.x - entry.group.position.x;
      const dz = player.position.z - entry.group.position.z;
      const d = Math.hypot(dx, dz);
      if (d < PROXIMITY && d < nearestDist) {
        nearest = entry;
        nearestDist = d;
      }
    });
    setActiveStation(nearest);

    renderer.render(scene, camera);
  }

  resize();
  camera.position.copy(player.position.clone().add(cameraOffset));
  animate();
})();
