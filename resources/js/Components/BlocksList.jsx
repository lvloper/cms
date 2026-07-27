import BlockWrapper from '@/Components/BlockWrapper'
import Text from '@/Blocks/Text'
import Media from '@/Blocks/Media'
import MediaText from '@/Blocks/MediaText'
import Cards from '@/Blocks/Cards'
import Search from '@/Blocks/Search'

const blockComponents = {
    Text,
    Media,
    MediaText,
    Cards,
    Search,
}

export default function BlocksList({ blocks = [] }) {
    return (
        <div className="bg-white blocks-container fade-in">
            {blocks.map((block, index) => {
                if (block.data?.hidden) return null

                const Component = blockComponents[block.type]
                if (!Component) return null

                return (
                    <BlockWrapper key={block.type + '-' + index} block={block}>
                        <Component {...block.data} />
                    </BlockWrapper>
                )
            })}
        </div>
    )
}
