@php
/**
 * Génère un avatar SVG inline avec les initiales — 100 % local, aucune
 * requête réseau (remplace ui-avatars.com).
 */
if (! function_exists('boutik_local_avatar')) {
    function boutik_local_avatar(string $name = '?', int $size = 32): string {
        $initials = '';
        $parts = preg_split('/\s+/', trim($name));
        foreach (array_slice(array_filter($parts), 0, 2) as $w) {
            $initials .= mb_strtoupper(mb_substr($w, 0, 1));
        }
        $initials = $initials !== '' ? $initials : '?';

        // Couleur de fond stable (dérivée du hash du nom)
        $palette = ['#1f7a8c','#bf4342','#7b2cbf','#2a9d8f','#e76f51','#264653','#588157','#003049','#9d0208','#3a0ca3'];
        $color = $palette[abs(crc32($name)) % count($palette)];

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 %1$d %1$d">'.
            '<rect width="%1$d" height="%1$d" rx="%2$d" fill="%3$s"/>'.
            '<text x="50%%" y="50%%" dy=".35em" text-anchor="middle" '.
            'font-family="-apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif" '.
            'font-size="%4$d" font-weight="600" fill="#ffffff">%5$s</text></svg>',
            $size,
            (int) round($size / 2),
            $color,
            (int) round($size * 0.42),
            htmlspecialchars($initials, ENT_QUOTES, 'UTF-8')
        );
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
@endphp
@foreach($members as $member)
	@if($loop->iteration < $max_count)
		@if(isset($member->media->display_url))
			<img class="user_avatar" src="{{$member->media->display_url}}" data-toggle="tooltip" title="{{$member->user_full_name}}">
		@else
			<img class="user_avatar" src="{{ boutik_local_avatar($member->first_name) }}" data-toggle="tooltip" title="{{$member->user_full_name}}">
		@endif
	@elseif($loop->iteration == $max_count)
		<img class="user_avatar" src="{{ boutik_local_avatar('+') }}" data-toggle="tooltip" title="...">
	@endif
@endforeach
