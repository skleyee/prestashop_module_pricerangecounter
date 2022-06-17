{if is_int($number_of_occurrences)}
<h2>
    The shop has {$number_of_occurrences} products included in the range
</h2>
{else}
<h2>
    {$number_of_occurrences}
</h2>
{/if}