{* Class Trees template for the PDF Converter *}
<text size="26" justification="centre"><C:rf:2Appendix A - Class Trees>Appendix A - Class Trees
</text>
{section name=classtrees loop=$trees}
<text size="16" justification="centre"><C:rf:3{$trees[classtrees].package}>Package {$trees[classtrees].package}
</text>
{section name=trees loop=$trees[classtrees].trees}
<text size="12"><C:IndexLetter:{$trees[classtrees].trees[trees].class}>
{$trees[classtrees].trees[trees].class_tree}</text>
{/section}
{/section}