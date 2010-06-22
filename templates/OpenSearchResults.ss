<ul class="opensearch-resultsBySource">
	<% control ResultsBySource %>
	<li>
		<h3>$Title</h3>
		<% if Results %>
			<ul class="opensearch-results">
				<% control Results %>
					<li>
						<h4><a href="$Link">$ShortName</a></h4>
						<div>$Content</div>
					</li>
				<% end_control %>
			</ul>
			
			<% if Results.MoreThanOnePage %>
			<div id="PageNumbers">
				<% if Results.NotLastPage %>
					<a class="next" href="$Results.NextLink">Next</a>
				<% end_if %>
				<% if Results.NotFirstPage %>
					<a class="prev" href="$Results.PrevLink">Prev</a>
				<% end_if %>
				<span>
					<% control Results.Pages %>
						<% if CurrentBool %>
							$PageNum
						<% else %>
							<a href="$Link">$PageNum</a>
						<% end_if %>
					<% end_control %>
				</span>
				<p>Page $Results.CurrentPage of $Results.TotalPages</p>
			</div>
				<% end_if %>
		<% else %>
		<p class="message"><% _t('OpenSearchResults.ss.NoResults', 'No results') %></p>
		<% end_if %>
	</li>
	<% end_control %>
</ul>