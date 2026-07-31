import BlockWrapper from '@/Components/BlockWrapper'
import Hero from '@/Blocks/Hero'
import Text from '@/Blocks/Text'
import Media from '@/Blocks/Media'
import MediaText from '@/Blocks/MediaText'
import Cards from '@/Blocks/Cards'
import Search from '@/Blocks/Search'
import ClientMarquee from '@/Blocks/ClientMarquee'
import ClientProjects from '@/Blocks/ClientProjects'
import ClientFeature from '@/Blocks/ClientFeature'
import ClientStatement from '@/Blocks/ClientStatement'
import ClientProcess from '@/Blocks/ClientProcess'
import ClientMetrics from '@/Blocks/ClientMetrics'
import ClientTestimonial from '@/Blocks/ClientTestimonial'
import ClientClosing from '@/Blocks/ClientClosing'

const blockComponents = {
    Hero,
    Text,
    Media,
    MediaText,
    Cards,
    Search,
    ClientMarquee,
    ClientProjects,
    ClientFeature,
    ClientStatement,
    ClientProcess,
    ClientMetrics,
    ClientTestimonial,
    ClientClosing,
}

export default function BlocksList({ blocks = [], client = null }) {
    return (
        <div className="blocks-container fade-in">
            {blocks.map((block, index) => {
                if (block.data?.hidden) return null

                const Component = blockComponents[block.type]
                if (!Component) return null

                return (
                    <BlockWrapper key={block.type + '-' + index} block={block}>
                        <Component {...block.data} client={client} />
                    </BlockWrapper>
                )
            })}
        </div>
    )
}
